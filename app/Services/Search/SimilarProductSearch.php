<?php

namespace App\Services\Search;

use App\Data\ImageInsight;
use App\Data\SmartOffer;
use App\Models\Product;
use App\Services\Geo\NearbyCatalog;
use Illuminate\Database\Eloquent\Builder;

class SimilarProductSearch
{
    public function __construct(
        private NearbyCatalog $nearby,
        private OfferAssembler $offers,
    ) {}

    /**
     * @return array{offers: array<int, SmartOffer>, nearest: ?SmartOffer, promotions: array<int, SmartOffer>}
     */
    public function search(ImageInsight $insight, ?float $lat, ?float $lng, string $sort, float $radiusKm): array
    {
        $products = $this->candidates($insight);
        $offers = $this->offers->sort(
            $this->offers->fromProducts($products, $insight, $lat, $lng),
            $sort,
        );

        return [
            'offers' => $offers,
            'nearest' => collect($offers)->first(fn (SmartOffer $offer) => $offer->nearest),
            'promotions' => $this->promotions($lat, $lng, $radiusKm),
        ];
    }

    /**
     * @return array<int, SmartOffer>
     */
    public function promotions(?float $lat, ?float $lng, float $radiusKm): array
    {
        $query = Product::query()
            ->published()
            ->whereHas('activePromotion')
            ->with([
                'shop.vendor.socialLinks',
                'brand',
                'category',
                'primaryImage',
                'variants',
                'activePromotion',
            ])
            ->withSum('inventories as stock_on_hand', 'quantity')
            ->withSum('inventories as stock_reserved', 'reserved');

        if ($lat !== null && $lng !== null) {
            $distances = $this->nearby->shopDistances($lat, $lng, $radiusKm);
            $query->whereIn('shop_id', array_keys($distances) ?: [0]);
        }

        $offers = $this->offers->fromProducts($query->limit(24)->get(), null, $lat, $lng);

        return array_values(array_filter(
            $this->offers->sort($offers, 'promotion'),
            fn (SmartOffer $offer) => $offer->discountPercent !== null && $offer->stock > 0,
        ));
    }

    /**
     * @return \Illuminate\Support\Collection<int, Product>
     */
    private function candidates(ImageInsight $insight): \Illuminate\Support\Collection
    {
        $signals = $insight->signals();

        if ($signals === []) {
            return collect();
        }

        return $this->baseQuery()
            ->where(function (Builder $query) use ($signals): void {
                foreach ($signals as $signal) {
                    $like = '%'.addcslashes($signal, '%_\\').'%';
                    $query->orWhere('products.name', 'like', $like)
                        ->orWhere('products.description', 'like', $like)
                        ->orWhereHas('brand', fn (Builder $brand) => $brand->where('name', 'like', $like))
                        ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', $like))
                        ->orWhereHas('variants', function (Builder $variant) use ($like): void {
                            $variant->where('name', 'like', $like)->orWhere('attributes', 'like', $like);
                        });
                }
            })
            ->limit(60)
            ->get();
    }

    private function baseQuery(): Builder
    {
        return Product::query()
            ->published()
            ->with([
                'shop.vendor.socialLinks',
                'brand',
                'category',
                'primaryImage',
                'variants',
                'activePromotion',
            ])
            ->withSum('inventories as stock_on_hand', 'quantity')
            ->withSum('inventories as stock_reserved', 'reserved');
    }

    /**
     * @return array<int, SmartOffer>
     */
    public function nearbyProducts(float $lat, float $lng, float $radiusKm, string $sort): array
    {
        $distances = $this->nearby->shopDistances($lat, $lng, $radiusKm);
        $products = $this->nearby->products($distances)->limit(60)->get();

        return $this->offers->sort(
            $this->offers->fromProducts($products, null, $lat, $lng),
            $sort === 'match' ? 'nearest' : $sort,
        );
    }
}
