<?php

namespace App\Http\Controllers\Catalog;

use App\Enums\CatalogStatus;
use App\Enums\ShopStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\CatalogSearchRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\OrderItem;
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
            ->with(['vendor.certifications' => fn ($query) => $query->where('status', 'approved')])
            ->withCount(['products' => fn ($query) => $query->published()])
            ->withAvg('reviews', 'rating')
            ->addSelect([
                'sales_count' => OrderItem::query()
                    ->selectRaw('count(distinct order_id)')
                    ->whereColumn('order_items.shop_id', 'shops.id'),
            ])
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

        $shop->load(['vendor.user', 'vendor.socialLinks', 'vendor.certifications' => fn ($query) => $query->where('status', 'approved')])
            ->loadCount(['products' => fn ($query) => $query->published()])
            ->loadAvg('reviews', 'rating');
        $shop->setAttribute('sales_count', (int) OrderItem::query()->where('shop_id', $shop->id)->selectRaw('count(distinct order_id) as aggregate')->value('aggregate'));

        $filters = $request->filters();
        $filters['shop'] = $shop->id;
        $catalogIds = $shop->products()->published()->get(['category_id', 'brand_id']);

        return view('pages.catalog.shops.show', [
            'shop' => $shop,
            'query' => $request->term(),
            'results' => $search->search($request->term(), $filters),
            'filters' => $filters,
            'categories' => Category::query()->where('status', CatalogStatus::Active)->whereIn('id', $catalogIds->pluck('category_id')->filter()->unique())->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->where('status', CatalogStatus::Active)->whereIn('id', $catalogIds->pluck('brand_id')->filter()->unique())->orderBy('name')->get(['id', 'name']),
            'contacts' => app(VendorContacts::class)->forShop($shop),
            'following' => $user && $user->shopFollows()->where('shop_id', $shop->id)->exists(),
        ]);
    }
}
