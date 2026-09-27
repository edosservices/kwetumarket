<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\CatalogSearchRequest;
use App\Models\AnalyticsEvent;
use App\Models\Brand;
use App\Models\Category;
use App\Models\DeliveryZone;
use App\Models\Product;
use App\Models\Shop;
use App\Services\Catalog\OfferPricing;
use App\Services\Catalog\StockLabel;
use App\Services\Catalog\VendorContacts;
use App\Services\Search\EloquentProductSearch;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(CatalogSearchRequest $request, EloquentProductSearch $search): View
    {
        return view('pages.catalog.products.index', [
            'query' => $request->term(),
            'results' => $search->search($request->term(), $request->filters()),
            'filters' => $request->filters(),
            'categories' => Category::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'shops' => Shop::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'city']),
            'cities' => Shop::query()->where('status', 'active')->whereNotNull('city')->orderBy('city')->pluck('city')->unique()->values(),
        ]);
    }

    public function show(Product $product): View
    {
        $user = auth()->user();

        if (! $product->isPubliclyVisible() && ($user === null || $user->cannot('view', $product))) {
            abort($user ? 403 : 404);
        }

        $product->load([
            'images',
            'variants' => fn ($query) => $query->with('image')->orderBy('name'),
            'shop.vendor.socialLinks',
            'supplierOffer',
            'brand',
            'category.parent',
            'activePromotion',
        ])->loadSum('inventories as stock_on_hand', 'quantity')
            ->loadSum('inventories as stock_reserved', 'reserved');

        AnalyticsEvent::query()->create([
            'name' => 'product_view',
            'user_id' => $user?->id,
            'subject_type' => Product::class,
            'subject_id' => $product->id,
        ]);

        return view('pages.catalog.products.show', [
            'product' => $product,
            'pricing' => OfferPricing::forProduct($product),
            'stockLabel' => StockLabel::make($product->availableQuantity()),
            'contacts' => app(VendorContacts::class)->forShop($product->shop, $product->name),
            'reviews' => $product->reviews()->with(['user:id,name', 'photos'])->latest()->limit(8)->get(),
            'similar' => Product::query()->published()->forCard()->where('category_id', $product->category_id)->whereKeyNot($product->id)->limit(4)->get(),
            'favorite' => $user && $user->favorites()->where('product_id', $product->id)->exists(),
            'deliveryFrom' => DeliveryZone::query()->where('is_active', true)->orderBy('fee')->first(),
        ]);
    }
}
