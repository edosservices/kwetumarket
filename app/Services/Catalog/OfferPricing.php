<?php

namespace App\Services\Catalog;

use App\Models\Product;

final class OfferPricing
{
    public function __construct(
        public int $finalPrice,
        public ?int $comparePrice,
        public ?int $discountPercent,
        public ?int $savings,
        public ?string $promotionEndsAt,
    ) {}

    public static function forProduct(Product $product): self
    {
        $price = (int) $product->price;
        $promotion = $product->relationLoaded('activePromotion')
            ? $product->activePromotion
            : $product->activePromotion()->first();

        $final = $price;
        $endsAt = null;

        if ($promotion && (int) $promotion->promotional_price < $price) {
            $final = (int) $promotion->promotional_price;
            $endsAt = $promotion->ends_at?->timezone(config('app.timezone'))->format('d/m/Y');
        }

        $before = null;

        if ($product->compare_at_price !== null && (int) $product->compare_at_price > $final) {
            $before = (int) $product->compare_at_price;
        } elseif ($final < $price) {
            $before = $price;
        }

        $savings = null;
        $discount = null;

        if ($before !== null && $before > $final) {
            $savings = $before - $final;
            $discount = intdiv($savings * 100, $before);
        }

        return new self($final, $before, $discount, $savings, $endsAt);
    }
}
