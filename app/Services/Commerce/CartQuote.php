<?php

namespace App\Services\Commerce;

final class CartQuote
{
    /**
     * @param  array<int, CartLine>  $lines
     */
    public function __construct(
        public array $lines,
        public int $subtotal,
        public int $discount,
        public int $deliveryFee,
        public int $tax,
        public int $total,
        public int $commission,
        public ?string $couponCode,
        public bool $blocked,
        public string $currency,
        public ?string $couponWarning,
    ) {}
}
