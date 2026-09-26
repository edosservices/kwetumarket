<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\ShopStatus;
use App\Enums\VendorStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\ShopRequest;
use App\Models\Shop;
use App\Models\Vendor;
use App\Services\Catalog\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        if ($user->hasRole('vendor') && ! $user->vendorProfile) {
            $user->vendorProfile()->create([
                'status' => VendorStatus::Pending,
            ]);
            $user->unsetRelation('vendorProfile');
        }

        $shop = $user->vendorProfile?->shops()->first();

        return view('pages.vendor.shop', [
            'shop' => $shop,
            'vendor' => $user->vendorProfile,
        ]);
    }

    public function update(ShopRequest $request, MediaStorage $media): RedirectResponse
    {
        $user = $request->user();
        $vendor = $user->vendorProfile;

        if (! $vendor && $user->hasRole('vendor')) {
            $vendor = Vendor::query()->create([
                'user_id' => $user->id,
                'status' => VendorStatus::Pending,
            ]);
        }

        abort_unless($vendor, 403);

        $shop = $vendor->shops()->first() ?? new Shop(['vendor_id' => $vendor->id]);
        $this->authorize($shop->exists ? 'update' : 'create', $shop->exists ? $shop : Shop::class);

        $shop->fill([
            'vendor_id' => $vendor->id,
            'name' => $request->string('name')->toString(),
            'slug' => $request->filled('slug') ? $request->string('slug')->toString() : $shop->slug,
            'description' => $request->input('description'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'location' => $request->input('location'),
            'status' => $user->can('shops.manage')
                ? $request->string('status')->toString()
                : ($shop->exists ? $shop->status : ShopStatus::Pending),
        ]);

        if ($request->hasFile('logo')) {
            $media->delete($shop->logo);
            $shop->logo = $media->store($request->file('logo'), 'shops/logos');
        }

        if ($request->hasFile('cover_image')) {
            $media->delete($shop->cover_image);
            $shop->cover_image = $media->store($request->file('cover_image'), 'shops/covers');
        }

        $shop->save();

        return redirect()->route('vendor.shop.edit')->with('status', __('ui.catalog.shop_saved'));
    }
}
