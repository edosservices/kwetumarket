<?php

namespace App\Services\Catalog;

final class StockLabel
{
    /**
     * @return array{label: string, tone: string}
     */
    public static function make(int $quantity): array
    {
        if ($quantity <= 0) {
            return [
                'label' => __('ui.smart.stock_out'),
                'tone' => 'out',
            ];
        }

        $pieces = trans_choice('ui.smart.stock_pieces', $quantity, ['count' => $quantity]);
        $low = (int) config('twende.nearby.low_stock');

        if ($quantity <= $low) {
            return [
                'label' => __('ui.smart.stock_low').' · '.$pieces,
                'tone' => 'low',
            ];
        }

        return [
            'label' => $pieces,
            'tone' => 'ok',
        ];
    }
}
