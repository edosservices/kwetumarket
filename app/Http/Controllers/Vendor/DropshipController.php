<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DropshipController extends Controller
{
    public function index(Request $request): View
    {
        $vendorId = $request->user()->vendorProfile?->id;
        abort_unless($vendorId, 403);

        $products = Product::query()
            ->where('is_dropship', true)
            ->whereHas('shop', fn ($query) => $query->where('vendor_id', $vendorId))
            ->orderBy('name')
            ->get();

        return view('pages.vendor.commerce.dropship', ['products' => $products]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $vendorId = $request->user()->vendorProfile?->id;
        abort_unless($vendorId && (int) $product->shop->vendor_id === (int) $vendorId, 403);
        abort_unless($product->is_dropship, 404);

        $data = $request->validate([
            'supplier_name' => ['nullable', 'string', 'max:160'],
            'supplier_sku' => ['nullable', 'string', 'max:64'],
            'supplier_price' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'price' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
        ]);

        $supplier = Money::toMinor($data['supplier_price']);
        $price = Money::toMinor($data['price']);

        $product->update([
            'supplier_name' => $data['supplier_name'] ?? null,
            'supplier_sku' => $data['supplier_sku'] ?? null,
            'supplier_price' => $supplier,
            'price' => $price,
        ]);

        return back()->with('success', __('experience.dropship_saved'));
    }
}
