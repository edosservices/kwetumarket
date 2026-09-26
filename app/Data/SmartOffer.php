<?php

namespace App\Data;

use App\Models\Product;

final class SmartOffer
{
    public function __construct(
        public Product $product,
        public int $matchPercent,
        public string $matchKind,
        public ?int $distanceMeters,
        public ?string $distanceLabel,
        public int $stock,
        public string $stockLabel,
        public string $stockTone,
        public int $finalPrice,
        public ?int $comparePrice,
        public ?int $discountPercent,
        public ?int $savings,
        public ?string $promotionEndsAt,
        public string $currency,
        public bool $nearest = false,
    ) {}

    public function finalFormatted(): string
    {
        return \App\Support\Money::format($this->finalPrice, $this->currency);
    }

    public function compareFormatted(): ?string
    {
        if ($this->comparePrice === null) {
            return null;
        }

        return \App\Support\Money::format($this->comparePrice, $this->currency);
    }

    public function savingsFormatted(): ?string
    {
        if ($this->savings === null) {
            return null;
        }

        return \App\Support\Money::format($this->savings, $this->currency);
    }
}
