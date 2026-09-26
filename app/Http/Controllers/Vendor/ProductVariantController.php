<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\ProductVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Catalog\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductVariantController extends Controller
{
    public function store(ProductVariantRequest $request, Product $product, StockService $stock): RedirectResponse
    {
        $variant = $product->variants()->create($request->variantAttributes());
        $this->applyOpeningStock($request, $stock, $product, $variant);

        return back()->with('status', __('ui.catalog.variant_saved'));
    }

    public function update(ProductVariantRequest $request, Product $product, ProductVariant $variant, StockService $stock): RedirectResponse
    {
        abort_unless((int) $variant->product_id === (int) $product->id, 404);
        $variant->update($request->variantAttributes());

        return back()->with('status', __('ui.catalog.variant_saved'));
    }

    public function destroy(Request $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->authorize('update', $product);
        abort_unless((int) $variant->product_id === (int) $product->id, 404);
        $variant->delete();

        return back()->with('status', __('ui.catalog.variant_deleted'));
    }

    private function applyOpeningStock(ProductVariantRequest $request, StockService $stock, Product $product, ProductVariant $variant): void
    {
        $opening = (int) $request->input('stock', 0);

        if ($opening < 1) {
            return;
        }

        try {
            $stock->record($product, $variant, StockMovementType::Purchase, $opening, $request->user(), $variant->sku, 'Stock variante');
        } catch (InsufficientStockException $exception) {
            throw ValidationException::withMessages(['stock' => $exception->getMessage()]);
        }
    }
}
