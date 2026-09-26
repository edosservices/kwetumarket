<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\ShopRequest;
use App\Models\Shop;
use App\Services\Catalog\ShopProfileUpdater;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Shop::class);

        $shops = Shop::query()
            ->with(['vendor.user:id,name,email'])
            ->withCount('products')
            ->orderBy('name')
            ->paginate(20);

        return view('pages.admin.shops.index', ['shops' => $shops]);
    }

    public function edit(Shop $shop): View
    {
        $this->authorize('update', $shop);

        $shop->load('vendor.socialLinks');

        return view('pages.admin.shops.form', ['shop' => $shop]);
    }

    public function update(ShopRequest $request, Shop $shop, ShopProfileUpdater $updater): RedirectResponse
    {
        $updater->update($shop, $request, true);

        return redirect()->route('admin.shops.index')->with('status', __('ui.catalog.shop_saved'));
    }
}
