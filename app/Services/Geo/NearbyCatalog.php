<?php

namespace App\Services\Geo;

use App\Enums\ShopStatus;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class NearbyCatalog
{
    /**
     * Shop ids inside the radius, keyed with the distance in meters.
     * A bounding box limits the rows before the distance check.
     *
     * @return array<int, float>
     */
    public function shopDistances(float $lat, float $lng, float $radiusKm): array
    {
        $box = GeoDistance::box($lat, $lng, $radiusKm * 1000);
        $query = Shop::query()
            ->where('status', ShopStatus::Active)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [$box['min_lat'], $box['max_lat']])
            ->whereBetween('longitude', [$box['min_lng'], $box['max_lng']]);

        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $expression = '(6371000 * acos(least(1, cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))))';

            return $query
                ->select('shops.id')
                ->selectRaw($expression.' as distance_m', [$lat, $lng, $lat])
                ->having('distance_m', '<=', $radiusKm * 1000)
                ->pluck('distance_m', 'id')
                ->map(fn ($meters) => (float) $meters)
                ->all();
        }

        $limit = $radiusKm * 1000;

        return $query
            ->limit(200)
            ->get(['id', 'latitude', 'longitude'])
            ->mapWithKeys(function (Shop $shop) use ($lat, $lng, $limit): array {
                $meters = GeoDistance::meters($lat, $lng, (float) $shop->latitude, (float) $shop->longitude);

                return $meters <= $limit ? [$shop->id => $meters] : [];
            })
            ->all();
    }

    /**
     * @param  array<int, float>  $distances
     */
    public function products(array $distances): Builder
    {
        return Product::query()
            ->published()
            ->whereIn('shop_id', array_keys($distances) ?: [0])
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
}
