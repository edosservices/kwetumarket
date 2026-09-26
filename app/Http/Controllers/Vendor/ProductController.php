<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\Catalog\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $this->abortUnlessOwnVendor($request, $product);
        $product->update($request->productAttributes());

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
