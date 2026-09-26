<?php

namespace App\Http\Controllers;

use App\Enums\CatalogStatus;
use App\Enums\ShopStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $published = fn () => Product::query()->published()->forCard();

        return view('pages.home', [
            'categories' => Category::query()
                ->whereNull('parent_id')
                ->where('status', CatalogStatus::Active)
                ->withCount(['children' => fn ($query) => $query->where('status', CatalogStatus::Active)])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(8)
                ->get(),
            'popular' => $published()->latest('published_at')->limit(4)->get(),
            'promotions' => $published()
                ->whereNotNull('compare_at_price')
                ->whereColumn('compare_at_price', '>', 'price')
                ->latest()
                ->limit(4)
                ->get(),
            'bestsellers' => $published()->latest('published_at')->limit(4)->get(),
            'shops' => Shop::query()
                ->where('status', ShopStatus::Active)
                ->withCount(['products' => fn ($query) => $query->published()])
                ->orderByDesc('products_count')
                ->orderBy('name')
                ->limit(4)
                ->get(),
            'newest' => $published()->latest()->limit(4)->get(),
        ]);
    }
}
