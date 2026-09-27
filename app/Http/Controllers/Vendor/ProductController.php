<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('products.view'), 403);

        $products = Product::query()
            ->where('vendor_id', $request->user()->vendorId())
            ->latest('id')
            ->paginate(12);

        return view('pages.vendor.products.index', [
            'products' => $products,
        ]);
    }

    public function show(Request $request, Product $product): View
    {
        $this->authorize('view', $product);

        return view('pages.vendor.products.show', [
            'product' => $product,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'price_minor' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $shop = Shop::query()->where('vendor_id', $request->user()->vendorId())->first();
        abort_unless($shop !== null, 403);

        $product = Product::query()->create([
            'vendor_id' => $request->user()->vendorId(),
            'shop_id' => $shop->id,
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'sku' => 'TWD-'.Str::upper(Str::random(8)),
            'status' => 'draft',
            'price_minor' => $data['price_minor'],
            'currency' => 'CDF',
            'stock' => $data['stock'],
        ]);

        return redirect()->route('vendor.products.show', $product);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'price_minor' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
        ]);

        $product->fill([
            'name' => $data['name'],
            'price_minor' => $data['price_minor'],
            'stock' => $data['stock'],
        ])->save();

        return redirect()->route('vendor.products.show', $product);
    }
}
