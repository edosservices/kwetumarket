<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\ProductStatus;
use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Catalog\PriceHistoryService;
use App\Services\Catalog\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $vendorId = $request->user()->vendorProfile?->id;
        abort_unless($vendorId, 403);

        $term = trim((string) $request->query('q', ''));

        $products = Product::query()
            ->whereHas('shop', fn ($query) => $query->where('vendor_id', $vendorId))
            ->with(['shop:id,name,slug', 'category:id,name', 'primaryImage'])
            ->withSum('inventories as stock_on_hand', 'quantity')
            ->withSum('inventories as stock_reserved', 'reserved')
            ->when($term !== '', function ($query) use ($term): void {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $query->where(fn ($inner) => $inner->where('name', 'like', $like)->orWhere('sku', 'like', $like));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('pages.vendor.products.index', [
            'products' => $products,
            'term' => $term,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Product::class);

        return view('pages.vendor.products.form', $this->formData($request, new Product));
    }

    public function store(ProductRequest $request, StockService $stock): RedirectResponse
    {
        $product = Product::query()->create($request->productAttributes());
        $this->seedStock($request, $stock, $product);

        return redirect()
            ->route('vendor.products.edit', $product)
            ->with('status', __('ui.catalog.product_saved'));
    }

    public function edit(Request $request, Product $product): View
    {
        $this->authorize('update', $product);
        $this->abortUnlessOwnVendor($request, $product);

        $product->load(['images', 'variants', 'shop']);

        return view('pages.vendor.products.form', $this->formData($request, $product));
    }

    public function update(ProductRequest $request, Product $product, PriceHistoryService $prices): RedirectResponse
    {
        $this->abortUnlessOwnVendor($request, $product);
        $before = [
            'price' => (int) $product->price,
            'compare_at_price' => $product->compare_at_price !== null ? (int) $product->compare_at_price : null,
        ];
        $attributes = $request->productAttributes();
        $product->update($attributes);
        $prices->record($product, null, $before, [
            'price' => (int) $attributes['price'],
            'compare_at_price' => $attributes['compare_at_price'],
        ], $request->user());

        return redirect()
            ->route('vendor.products.edit', $product)
            ->with('status', __('ui.catalog.product_saved'));
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);
        $this->abortUnlessOwnVendor($request, $product);

        if ($product->status->value === 'draft') {
            $product->delete();
        } else {
            $product->update([
                'status' => 'archived',
                'published_at' => null,
            ]);
        }

        return redirect()->route('vendor.products.index')->with('status', __('ui.catalog.product_archived'));
    }

    public function duplicate(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);
        $this->abortUnlessOwnVendor($request, $product);
        $copy = DB::transaction(function () use ($product): Product {
            $product->load(['variants', 'images']);
            $sku = $this->freshSku($product->sku);
            $clone = $product->replicate(['slug', 'published_at']);
            $clone->sku = $sku;
            $clone->slug = Product::nextAvailableSlug($product->name.' copie');
            $clone->status = ProductStatus::Draft;
            $clone->published_at = null;
            $clone->save();

            foreach ($product->variants as $variant) {
                $variantCopy = $variant->replicate();
                $variantCopy->product_id = $clone->id;
                $variantCopy->sku = $this->freshSku($variant->sku);
                $variantCopy->stock = 0;
                $variantCopy->save();
            }

            foreach ($product->images as $image) {
                if (! Storage::disk($image->disk)->exists($image->path)) {
                    continue;
                }

                $target = 'products/'.$clone->id.'/'.basename($image->path);
                Storage::disk($image->disk)->copy($image->path, $target);
                $clone->images()->create([
                    'disk' => $image->disk,
                    'path' => $target,
                    'original_path' => $image->original_path,
                    'thumb_path' => $image->thumb_path,
                    'alt_text' => $image->alt_text,
                    'is_primary' => $image->is_primary,
                    'sort_order' => $image->sort_order,
                ]);
            }

            return $clone;
        });

        return redirect()->route('vendor.products.edit', $copy)->with('status', __('operations.duplicated'));
    }

    public function status(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);
        $this->abortUnlessOwnVendor($request, $product);
        $status = (string) $request->validate([
            'status' => ['required', 'in:draft,pending,published,archived'],
        ])['status'];

        $product->update([
            'status' => $status,
            'published_at' => $status === 'published' ? ($product->published_at ?? now()) : null,
        ]);

        return back()->with('status', __('operations.status_saved'));
    }

    private function freshSku(string $sku): string
    {
        $base = substr($sku, 0, 50);

        do {
            $next = $base.'-'.strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        } while (Product::query()->where('sku', $next)->exists() || ProductVariant::query()->where('sku', $next)->exists());

        return $next;
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Request $request, Product $product): array
    {
        $vendorId = $request->user()->vendorProfile?->id;

        return [
            'product' => $product,
            'shops' => $request->user()->vendorProfile?->shops()->orderBy('name')->pluck('name', 'id') ?? collect(),
            'categories' => Category::query()->orderBy('name')->pluck('name', 'id'),
            'brands' => Brand::query()->orderBy('name')->pluck('name', 'id'),
            'vendorId' => $vendorId,
            'action' => $product->exists ? route('vendor.products.update', $product) : route('vendor.products.store'),
            'cancel' => route('vendor.products.index'),
            'moderate' => false,
        ];
    }

    private function abortUnlessOwnVendor(Request $request, Product $product): void
    {
        if ($request->user()->can('products.manage')) {
            return;
        }

        $product->loadMissing('shop');
        abort_unless((int) $product->shop->vendor_id === (int) $request->user()->vendorProfile?->id, 403);
    }

    private function seedStock(ProductRequest $request, StockService $stock, Product $product): void
    {
        $initial = (int) $request->input('initial_stock', 0);

        if ($initial < 1) {
            return;
        }

        try {
            $stock->record($product, null, StockMovementType::Purchase, $initial, $request->user(), null, 'Stock initial');
        } catch (InsufficientStockException $exception) {
            throw ValidationException::withMessages([
                'initial_stock' => $exception->getMessage(),
            ]);
        }
    }
}
