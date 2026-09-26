<?php

namespace App\Services\Search;

use App\Data\ImageInsight;
use App\Data\SmartOffer;
use App\Models\Product;
use App\Services\Catalog\OfferPricing;
use App\Services\Catalog\StockLabel;
use App\Services\Geo\GeoDistance;
use Illuminate\Support\Collection;

class OfferAssembler
{
    /**
     * @param  Collection<int, Product>  $products
     * @return array<int, SmartOffer>
     */
    public function fromProducts(Collection $products, ?ImageInsight $insight, ?float $lat, ?float $lng): array
    {
        return $products
            ->map(fn (Product $product) => $this->offer($product, $insight, $lat, $lng))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, SmartOffer>  $offers
     * @return array<int, SmartOffer>
     */
    public function sort(array $offers, string $sort): array
    {
        $items = collect($offers);

        $sorted = $items->sort(function (SmartOffer $left, SmartOffer $right) use ($sort): int {
            return match ($sort) {
                'nearest' => ($left->distanceMeters ?? PHP_INT_MAX) <=> ($right->distanceMeters ?? PHP_INT_MAX),
                'price' => $left->finalPrice <=> $right->finalPrice,
                'stock' => $right->stock <=> $left->stock,
                'promotion' => ($right->discountPercent ?? -1) <=> ($left->discountPercent ?? -1),
                'popular' => ($right->product->published_at?->getTimestamp() ?? 0) <=> ($left->product->published_at?->getTimestamp() ?? 0),
                default => $right->matchPercent <=> $left->matchPercent,
            };
        });

        return $this->markNearest($sorted->values()->all());
    }

    private function offer(Product $product, ?ImageInsight $insight, ?float $lat, ?float $lng): ?SmartOffer
    {
        $match = $insight ? $this->score($product, $insight) : ['percent' => 0, 'kind' => 'similar'];

        if ($insight && $match['percent'] <= 0) {
            return null;
        }

        $meters = null;

        if ($lat !== null && $lng !== null && $product->shop?->latitude !== null && $product->shop?->longitude !== null) {
            $meters = GeoDistance::meters($lat, $lng, (float) $product->shop->latitude, (float) $product->shop->longitude);
        }

        $pricing = OfferPricing::forProduct($product);
        $stock = $product->availableQuantity();
        $stockLabel = StockLabel::make($stock);

        return new SmartOffer(
            product: $product,
            matchPercent: $match['percent'],
            matchKind: $match['kind'],
            distanceMeters: $meters === null ? null : (int) round($meters),
            distanceLabel: GeoDistance::format($meters),
            stock: $stock,
            stockLabel: $stockLabel['label'],
            stockTone: $stockLabel['tone'],
            finalPrice: $pricing->finalPrice,
            comparePrice: $pricing->comparePrice,
            discountPercent: $pricing->discountPercent,
            savings: $pricing->savings,
            promotionEndsAt: $pricing->promotionEndsAt,
            currency: (string) $product->currency,
        );
    }

    /**
     * @return array{percent: int, kind: string}
     */
    private function score(Product $product, ImageInsight $insight): array
    {
        $blob = mb_strtolower(implode(' ', array_filter([
            $product->name,
            $product->description,
            $product->brand?->name,
            $product->category?->name,
            $product->variants->pluck('name')->implode(' '),
            $product->variants->map(fn ($variant) => json_encode($variant->attributes))->implode(' '),
        ])));

        $percent = 0;
        $brand = mb_strtolower((string) $insight->brand);
        $model = mb_strtolower((string) $insight->model);
        $category = mb_strtolower((string) $insight->category);

        if ($brand !== '' && str_contains($blob, $brand)) {
            $percent += 35;
        }

        if ($model !== '' && str_contains($blob, $model)) {
            $percent += 30;
        }

        if ($category !== '' && str_contains($blob, $category)) {
            $percent += 15;
        }

        $color = mb_strtolower((string) $insight->color);

        if ($color !== '' && str_contains($blob, $color)) {
            $percent += 12;
        }

        $keywordHits = 0;

        foreach ($insight->signals() as $signal) {
            if (str_contains($blob, $signal)) {
                $keywordHits++;
            }
        }

        $percent += min(24, $keywordHits * 8);
        $percent = min(100, $percent);

        if ($insight->limited) {
            $percent = min(70, $percent);
        }

        $kind = (! $insight->limited && $insight->confidence >= 0.85 && $percent >= 90)
            ? 'matching'
            : 'similar';

        return ['percent' => $percent, 'kind' => $kind];
    }

    /**
     * @param  array<int, SmartOffer>  $offers
     * @return array<int, SmartOffer>
     */
    private function markNearest(array $offers): array
    {
        $winner = null;

        foreach ($offers as $offer) {
            $offer->nearest = false;

            if ($offer->stock <= 0 || $offer->distanceMeters === null) {
                continue;
            }

            if ($winner === null || $offer->distanceMeters < $winner->distanceMeters) {
                $winner = $offer;
            }
        }

        if ($winner !== null) {
            $winner->nearest = true;
        }

        return $offers;
    }
}
