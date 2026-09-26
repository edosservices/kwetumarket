<?php

namespace App\Http\Controllers\Catalog;

use App\Enums\ShopStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\CatalogSearchRequest;
use App\Models\Shop;
use App\Services\Catalog\VendorContacts;
use App\Services\Search\EloquentProductSearch;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(): View
    {
        $shops = Shop::query()
            ->where('status', ShopStatus::Active)
            ->withCount(['products' => fn ($query) => $query->published()])
            ->orderBy('name')
            ->paginate(12);

        return view('pages.catalog.shops.index', [
            'shops' => $shops,
        ]);
    }

    public function show(CatalogSearchRequest $request, Shop $shop, EloquentProductSearch $search): View
    {
        $user = auth()->user();

        if (! $shop->isPublic() && ($user === null || $user->cannot('view', $shop))) {
            abort($user ? 403 : 404);
        }

        $shop->load('vendor.user', 'vendor.socialLinks')->loadCount(['products' => fn ($query) => $query->published()]);

        $filters = $request->filters();
        $filters['shop'] = $shop->id;

        return view('pages.catalog.shops.show', [
            'shop' => $shop,
            'query' => $request->term(),
            'results' => $search->search($request->term(), $filters),
            'contacts' => app(VendorContacts::class)->forShop($shop),
        ]);
    }
}
