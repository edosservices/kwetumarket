<?php

namespace App\Http\Controllers;

use App\Contracts\ProductSearch;
use App\Enums\CatalogStatus;
use App\Enums\ShopStatus;
use App\Http\Requests\Catalog\CatalogSearchRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Shop;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(CatalogSearchRequest $request, ProductSearch $search): View
    {
        $results = $search->search($request->term(), $request->filters());
        $results['paginator'] ??= null;

        return view('pages.search', [
            'query' => $request->term(),
            'results' => $results,
            'filters' => $request->filters(),
            'categories' => Category::query()->where('status', CatalogStatus::Active)->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->where('status', CatalogStatus::Active)->orderBy('name')->get(['id', 'name']),
            'shops' => Shop::query()->where('status', ShopStatus::Active)->orderBy('name')->get(['id', 'name', 'city']),
            'cities' => Shop::query()->where('status', ShopStatus::Active)->whereNotNull('city')->orderBy('city')->pluck('city')->unique()->values(),
        ]);
    }
}
