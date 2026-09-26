<?php

namespace App\Models;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Models\Concerns\HasPublicSlug;
use App\Support\Money;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasPublicSlug;

    protected $fillable = [
        'shop_id',
        'category_id',
        'brand_id',
        'name',
        'slug',
        'sku',
        'description',
        'price',
        'compare_at_price',
        'currency',
        'status',
        'condition',
        'weight',
        'is_dropship',
        'supplier_name',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'compare_at_price' => 'integer',
            'weight' => 'integer',
            'is_dropship' => 'boolean',
            'status' => ProductStatus::class,
            'condition' => ProductCondition::class,
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::saving(function (self $product): void {
            if ($product->status === ProductStatus::Published && $product->published_at === null) {
                $product->published_at = now();
            }
        });
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class);
    }

    public function activePromotion(): HasOne
    {
        return $this->hasOne(Promotion::class)
            ->where('is_active', true)
            ->where(function (Builder $query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->latest('id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', ProductStatus::Published)
            ->whereHas('shop', fn (Builder $shop) => $shop->where('status', 'active'));
    }

    public function scopeForCard(Builder $query): Builder
    {
        return $query
            ->select([
                'products.id',
                'products.shop_id',
                'products.category_id',
                'products.brand_id',
                'products.name',
                'products.slug',
                'products.sku',
                'products.price',
                'products.compare_at_price',
                'products.currency',
                'products.status',
                'products.condition',
                'products.is_dropship',
                'products.created_at',
            ])
            ->with([
                'shop.vendor.certifications' => fn ($query) => $query->where('status', 'approved'),
                'brand',
                'category',
                'primaryImage',
            ])
            ->withAvg('reviews', 'rating')
            ->withExists('variants as has_variants')
            ->withSum('inventories as stock_on_hand', 'quantity')
            ->withSum('inventories as stock_reserved', 'reserved');
    }

    public function formattedPrice(): string
    {
        return Money::amount((int) $this->price);
    }

    public function formattedComparePrice(): ?string
    {
        if ($this->compare_at_price === null) {
            return null;
        }

        return Money::amount((int) $this->compare_at_price);
    }

    public function discountPercent(): ?int
    {
        if ($this->compare_at_price === null || $this->compare_at_price <= $this->price) {
            return null;
        }

        return intdiv(((int) $this->compare_at_price - (int) $this->price) * 100, (int) $this->compare_at_price);
    }

    public function availableQuantity(): int
    {
        $onHand = $this->stock_on_hand ?? $this->inventories()->sum('quantity');
        $reserved = $this->stock_reserved ?? $this->inventories()->sum('reserved');

        return max(0, (int) $onHand - (int) $reserved);
    }

    public function isPubliclyVisible(): bool
    {
        return $this->status === ProductStatus::Published
            && $this->shop?->isPublic();
    }
}
