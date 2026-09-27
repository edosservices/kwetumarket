<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Shop;
use App\Models\SupplierOffer;
use App\Services\Catalog\DropshipService;
use App\Services\Catalog\MediaStorage;
use App\Services\Catalog\PriceHistoryService;
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

        return view('pages.vendor.commerce.dropship', [
            'products' => $products,
            'offers' => SupplierOffer::query()->where('is_active', true)->where('vendor_id', '!=', $vendorId)->with('vendor:id,business_name')->latest()->get(),
            'mine' => SupplierOffer::query()->where('vendor_id', $vendorId)->latest()->get(),
            'shops' => $request->user()->vendorProfile->shops()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function offer(Request $request, MediaStorage $media): RedirectResponse
    {
        $vendorId = $request->user()->vendorProfile?->id;
        abort_unless($vendorId, 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'sku' => ['required', 'string', 'max:64', 'unique:supplier_offers,sku'],
            'description' => ['required', 'string', 'max:5000'],
            'price' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'lead_days' => ['required', 'integer', 'min:0', 'max:365'],
            'moq' => ['required', 'integer', 'min:1', 'max:100000'],
            'image' => ['nullable', 'file', 'max:'.config('twende.media.max_kilobytes'), 'mimes:jpg,jpeg,png,webp'],
        ]);
        $path = $request->hasFile('image') ? $media->store($request->file('image'), 'suppliers/'.$vendorId) : null;
        SupplierOffer::query()->create([
            'vendor_id' => $vendorId,
            'name' => $data['name'],
            'sku' => $data['sku'],
            'description' => $data['description'],
            'price' => Money::toMinor($data['price']),
            'currency' => 'CDF',
            'stock' => $data['stock'],
            'lead_days' => $data['lead_days'],
            'moq' => $data['moq'],
            'is_active' => true,
            'image_disk' => $path ? $media->disk() : null,
            'image_path' => $path,
        ]);

        return back()->with('success', __('operations.offer_saved'));
    }

    public function sync(Request $request, SupplierOffer $offer): RedirectResponse
    {
        abort_unless((int) $offer->vendor_id === (int) $request->user()->vendorProfile?->id, 403);
        $data = $request->validate([
            'price' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['required', 'boolean'],
        ]);
        $offer->update([
            'price' => Money::toMinor($data['price']),
            'stock' => $data['stock'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', __('operations.offer_saved'));
    }

    public function import(Request $request, SupplierOffer $offer, DropshipService $dropship): RedirectResponse
    {
        $vendorId = $request->user()->vendorProfile?->id;
        abort_unless($vendorId, 403);
        $data = $request->validate([
            'shop_id' => ['required', 'integer'],
            'price' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
            'publish' => ['sometimes', 'boolean'],
            'stock_sync' => ['sometimes', 'boolean'],
        ]);
        $shop = Shop::query()->where('vendor_id', $vendorId)->whereKey($data['shop_id'])->first();
        abort_unless($shop, 403);
        $dropship->import(
            $shop,
            $offer,
            $request->user(),
            Money::toMinor($data['price']),
            $request->boolean('publish'),
            $request->boolean('stock_sync', true),
        );

        return back()->with('success', __('operations.imported'));
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
        $before = ['price' => (int) $product->price];

        $product->update([
            'supplier_name' => $data['supplier_name'] ?? null,
            'supplier_sku' => $data['supplier_sku'] ?? null,
            'supplier_price' => $supplier,
            'price' => $price,
        ]);
        app(PriceHistoryService::class)->record($product, null, $before, ['price' => $price], $request->user());

        return back()->with('success', __('experience.dropship_saved'));
    }
}
