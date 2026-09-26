<?php

namespace App\Models;

use App\Enums\VariantStatus;
use App\Support\Money;
use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'attributes',
        'price',
        'stock',
        'image_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'price' => 'integer',
            'stock' => 'integer',
            'status' => VariantStatus::class,
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(ProductImage::class, 'image_id');
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    public function effectivePrice(): int
    {
        return $this->price ?? (int) $this->product->price;
    }

    public function formattedPrice(): string
    {
        return Money::amount($this->effectivePrice());
    }
}
