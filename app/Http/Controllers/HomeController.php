<?php

namespace App\Http\Controllers;

use App\Enums\CatalogStatus;
use App\Enums\ShopStatus;
use App\Enums\VendorStatus;
use App\Models\AdCampaign;
use App\Models\AnalyticsEvent;
use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        if (! session('twende_visit')) {
            session(['twende_visit' => true]);
            AnalyticsEvent::query()->create([
                'name' => 'visit',
                'user_id' => auth()->id(),
            ]);
        }

        $published = fn () => Product::query()->published()->forCard();

        return view('pages.home', [
            'categories' => Category::query()
                ->whereNull('parent_id')
                ->where('status', CatalogStatus::Active)
                ->withCount(['children' => fn ($query) => $query->where('status', CatalogStatus::Active)])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(12)
                ->get(),
            'popular' => $published()->orderByDesc('reviews_avg_rating')->latest('published_at')->limit(8)->get(),
            'promotions' => $published()
                ->where(function ($query): void {
                    $query->where(function ($priced): void {
                        $priced->whereNotNull('compare_at_price')->whereColumn('compare_at_price', '>', 'price');
                    })->orWhereHas('activePromotion');
                })
                ->latest()
                ->limit(8)
                ->get(),
            'bestsellers' => $published()
                ->withSum('orderItems as units_sold', 'quantity')
                ->orderByRaw('coalesce(units_sold, 0) desc')
                ->latest('published_at')
                ->limit(8)
                ->get(),
            'shops' => Shop::query()
                ->where('status', ShopStatus::Active)
                ->with(['vendor.certifications' => fn ($query) => $query->where('status', 'approved')])
                ->withCount(['products' => fn ($query) => $query->published()])
                ->withAvg('reviews', 'rating')
                ->addSelect([
                    'sales_count' => OrderItem::query()
                        ->selectRaw('count(distinct order_id)')
                        ->whereColumn('order_items.shop_id', 'shops.id'),
                ])
                ->orderByDesc('products_count')
                ->orderBy('name')
                ->limit(8)
                ->get(),
            'newest' => $published()->latest()->limit(8)->get(),
            'ads' => AdCampaign::query()->visible()->with('vendor.user:id,name')->latest()->limit(2)->get(),
            'vendors' => Vendor::query()
                ->where('status', VendorStatus::Active)
                ->with([
                    'user:id,name',
                    'certifications' => fn ($query) => $query->where('status', 'approved'),
                    'shops' => fn ($query) => $query
                        ->where('status', ShopStatus::Active)
                        ->withCount(['products' => fn ($products) => $products->published()])
                        ->withAvg('reviews', 'rating'),
                ])
                ->limit(12)
                ->get()
                ->sortByDesc(fn (Vendor $vendor) => (int) $vendor->shops->max('products_count'))
                ->take(8)
                ->values(),
            'slides' => HeroSlide::query()->visible('home')->get(),
            'flash' => $published()->whereHas('activePromotion', fn ($query) => $query->whereNotNull('ends_at')->where('ends_at', '<=', now()->addDays(3)))->limit(4)->get(),
            'recommended' => $this->recommended($published),
        ]);
    }

    /**
     * @param  callable(): Builder<Product>  $published
     * @return Collection<int, Product>
     */
    private function recommended(callable $published): Collection
    {
        $query = $published();
        $user = auth()->user();

        if ($user) {
            $categoryIds = $user->favorites()
                ->with('product:id,category_id')
                ->get()
                ->pluck('product.category_id')
                ->filter()
                ->unique()
                ->values();

            if ($categoryIds->isNotEmpty()) {
                $query->whereIn('category_id', $categoryIds);
            }
        }

        return $query->latest()->limit(8)->get();
    }
}
