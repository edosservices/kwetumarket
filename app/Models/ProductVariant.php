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
        'color_name',
        'color_hex',
        'size',
        'attributes',
        'price',
        'promotional_price',
        'stock',
        'image_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'price' => 'integer',
            'promotional_price' => 'integer',
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

    public function salePrice(): int
    {
        $base = $this->effectivePrice();

        if ($this->promotional_price !== null && (int) $this->promotional_price > 0 && (int) $this->promotional_price < $base) {
            return (int) $this->promotional_price;
        }

        return $base;
    }

    public function formattedPrice(): string
    {
        return Money::amount($this->effectivePrice());
    }

    public function formattedSalePrice(): string
    {
        return Money::amount($this->salePrice());
    }

    public function colorLabel(): ?string
    {
        $extra = $this->getAttribute('attributes');

        return $this->color_name ?: (is_array($extra) ? ($extra['color'] ?? null) : null);
    }

    public function sizeLabel(): ?string
    {
        $extra = $this->getAttribute('attributes');

        return $this->size ?: (is_array($extra) ? ($extra['size'] ?? null) : null);
    }
}
