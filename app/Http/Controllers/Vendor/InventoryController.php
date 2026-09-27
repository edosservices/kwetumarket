<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StockMovementRequest;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Services\Catalog\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $vendorId = $request->user()->vendorProfile?->id;
        abort_unless($vendorId, 403);

        $movements = StockMovement::query()
            ->whereHas('product.shop', fn ($query) => $query->where('vendor_id', $vendorId))
            ->with(['product:id,name,slug', 'variant:id,name', 'user:id,name'])
            ->latest()
            ->paginate(20);

        $products = Product::query()
            ->whereHas('shop', fn ($query) => $query->where('vendor_id', $vendorId))
            ->with('variants:id,product_id,name')
            ->orderBy('name')
            ->get(['id', 'name']);

        $threshold = (int) config('twende.nearby.low_stock', 3);
        $alerts = Inventory::query()
            ->whereHas('product.shop', fn ($query) => $query->where('vendor_id', $vendorId))
            ->with(['product:id,name', 'variant:id,name'])
            ->get()
            ->filter(fn (Inventory $row) => $row->available() <= $threshold);

        return view('pages.vendor.inventory.index', [
            'movements' => $movements,
            'products' => $products,
            'alerts' => $alerts,
            'threshold' => $threshold,
        ]);
    }

    public function store(StockMovementRequest $request, StockService $stock): RedirectResponse
    {
        $product = Product::query()->findOrFail($request->integer('product_id'));
        $this->authorize('update', $product);
        $this->guardVendor($request, $product);

        $variant = null;

        if ($request->filled('variant_id')) {
            $variant = ProductVariant::query()->findOrFail($request->integer('variant_id'));
            abort_unless((int) $variant->product_id === (int) $product->id, 404);
        }

        try {
            $stock->record(
                $product,
                $variant,
                StockMovementType::from((string) $request->input('type')),
                $request->integer('quantity'),
                $request->user(),
                $request->input('reference'),
                $request->input('comment'),
            );
        } catch (InsufficientStockException $exception) {
            throw ValidationException::withMessages(['quantity' => __('ui.catalog.stock_insufficient')]);
        }

        return back()->with('status', __('ui.catalog.stock_saved'));
    }

    private function guardVendor(Request $request, Product $product): void
    {
        if ($request->user()->can('products.manage')) {
            return;
        }

        $product->loadMissing('shop');
        abort_unless((int) $product->shop->vendor_id === (int) $request->user()->vendorProfile?->id, 403);
    }
}
