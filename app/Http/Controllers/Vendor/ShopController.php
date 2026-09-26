<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\ShopStatus;
use App\Enums\VendorStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\ShopRequest;
use App\Models\Shop;
use App\Models\Vendor;
use App\Services\Catalog\ShopProfileUpdater;
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

        $vendor = $user->vendorProfile?->load('socialLinks');
        $shop = $vendor?->shops()->first();

        return view('pages.vendor.shop', [
            'shop' => $shop,
            'vendor' => $vendor,
        ]);
    }

    public function update(ShopRequest $request, ShopProfileUpdater $updater): RedirectResponse
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
        $shop->vendor_id = $vendor->id;
        $shop->status = $user->can('shops.manage')
            ? $request->string('status')->toString()
            : ($shop->exists ? $shop->status : ShopStatus::Pending);

        $updater->update($shop, $request, $user->can('shops.manage'));

        return redirect()->route('vendor.shop.edit')->with('status', __('ui.catalog.shop_saved'));
    }
}
