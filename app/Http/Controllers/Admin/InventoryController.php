<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StockMovementRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Services\Catalog\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', \App\Models\Inventory::class);

        return view('pages.admin.inventory.index', [
            'movements' => StockMovement::query()
                ->with(['product:id,name,slug', 'variant:id,name', 'user:id,name'])
                ->latest()
                ->paginate(30),
            'products' => Product::query()->with('variants:id,product_id,name')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StockMovementRequest $request, StockService $stock): RedirectResponse
    {
        $product = Product::query()->findOrFail($request->integer('product_id'));
        $variant = $request->filled('variant_id')
            ? ProductVariant::query()->findOrFail($request->integer('variant_id'))
            : null;

        if ($variant) {
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
        } catch (InsufficientStockException) {
            return back()->withErrors(['quantity' => __('ui.catalog.stock_insufficient')])->withInput();
        }

        return back()->with('status', __('ui.catalog.stock_saved'));
    }
}
