<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\ShopRequest;
use App\Models\Shop;
use App\Services\Catalog\MediaStorage;
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

        return view('pages.admin.shops.form', ['shop' => $shop]);
    }

    public function update(ShopRequest $request, Shop $shop, MediaStorage $media): RedirectResponse
    {
        $data = $request->safe()->except(['logo', 'cover_image', 'slug']);

        if ($request->filled('slug')) {
            $data['slug'] = $request->string('slug')->toString();
        }

        $shop->fill($data);

        if ($request->hasFile('logo')) {
            $media->delete($shop->logo);
            $shop->logo = $media->store($request->file('logo'), 'shops/logos');
        }

        if ($request->hasFile('cover_image')) {
            $media->delete($shop->cover_image);
            $shop->cover_image = $media->store($request->file('cover_image'), 'shops/covers');
        }

        $shop->save();

        return redirect()->route('admin.shops.index')->with('status', __('ui.catalog.shop_saved'));
    }
}
