<?php

namespace App\Http\Controllers;

use App\Enums\CatalogStatus;
use App\Enums\ShopStatus;
use App\Enums\VendorStatus;
use App\Models\AdCampaign;
use App\Models\AnalyticsEvent;
use App\Models\Category;
use App\Models\HeroSlide;
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
                ->limit(8)
                ->get(),
            'popular' => $published()->latest('published_at')->limit(4)->get(),
            'promotions' => $published()
                ->where(function ($query): void {
                    $query->where(function ($priced): void {
                        $priced->whereNotNull('compare_at_price')->whereColumn('compare_at_price', '>', 'price');
                    })->orWhereHas('activePromotion');
                })
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
            'ads' => AdCampaign::query()->visible()->with('vendor.user:id,name')->latest()->limit(2)->get(),
            'vendors' => Vendor::query()->where('status', VendorStatus::Active)->with(['user:id,name', 'certifications'])->limit(4)->get(),
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

        return $query->latest()->limit(4)->get();
    }
}
