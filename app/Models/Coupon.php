<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_subtotal', 'starts_at', 'ends_at',
        'is_active', 'usage_limit', 'used_count',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'min_subtotal' => 'integer',
            'is_active' => 'boolean',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function accepts(int $subtotal): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at !== null && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at !== null && $this->ends_at->isPast()) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return $subtotal >= (int) $this->min_subtotal;
    }

    public function discountFor(int $subtotal): int
    {
        if ($this->type === 'percent') {
            return intdiv($subtotal * (int) $this->value, 100);
        }

        return min($subtotal, (int) $this->value);
    }
}
