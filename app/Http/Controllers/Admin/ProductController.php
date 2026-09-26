<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $term = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');

        $products = Product::query()
            ->with(['shop:id,name,slug', 'category:id,name', 'brand:id,name', 'primaryImage'])
            ->withSum('inventories as stock_on_hand', 'quantity')
            ->withSum('inventories as stock_reserved', 'reserved')
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($term !== '', function ($query) use ($term): void {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $query->where(fn ($inner) => $inner->where('name', 'like', $like)->orWhere('sku', 'like', $like));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.admin.products.index', [
            'products' => $products,
            'term' => $term,
            'status' => $status,
        ]);
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);
        $product->load(['images', 'variants', 'shop']);

        return view('pages.vendor.products.form', [
            'product' => $product,
            'shops' => Shop::query()->orderBy('name')->pluck('name', 'id'),
            'categories' => Category::query()->orderBy('name')->pluck('name', 'id'),
            'brands' => Brand::query()->orderBy('name')->pluck('name', 'id'),
            'action' => route('admin.products.update', $product),
            'cancel' => route('admin.products.index'),
            'moderate' => true,
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->productAttributes());

        return redirect()->route('admin.products.edit', $product)->with('status', __('ui.catalog.product_saved'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', __('ui.catalog.product_deleted'));
    }
}
