<?php

namespace App\Services\Catalog;

use App\Models\PriceChange;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

class PriceHistoryService
{
    /**
     * @param  array<string, int|null>  $before
     * @param  array<string, int|null>  $after
     */
    public function record(Product $product, ?ProductVariant $variant, array $before, array $after, ?User $actor): void
    {
        foreach (['price', 'promotional_price', 'compare_at_price'] as $field) {
            if (! array_key_exists($field, $after)) {
                continue;
            }

            $previous = $before[$field] ?? null;
            $current = $after[$field];

            if ($previous === $current || (int) $previous === (int) $current) {
                continue;
            }

            PriceChange::query()->create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'user_id' => $actor?->id,
                'field' => $field,
                'amount_before' => $previous,
                'amount_after' => $current,
                'currency' => $product->currency,
                'created_at' => now(),
            ]);
        }
    }
}
