<?php

namespace App\Services\Commerce;

use App\Models\CartItem;

final class CartLine
{
    public function __construct(
        public CartItem $item,
        public int $unitPrice,
        public int $lineTotal,
        public int $available,
        public bool $short,
        public int $commission,
    ) {}
}
