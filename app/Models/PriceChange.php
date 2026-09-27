<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class PriceChange extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id', 'product_variant_id', 'user_id', 'field', 'amount_before', 'amount_after', 'currency', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_before' => 'integer',
            'amount_after' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new LogicException('Price history cannot be rewritten.');
        });

        static::deleting(function (): void {
            throw new LogicException('Price history cannot be deleted.');
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
