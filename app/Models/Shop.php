<?php

namespace App\Models;

use App\Enums\ShopStatus;
use App\Models\Concerns\HasPublicSlug;
use Database\Factories\ShopFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Storage;

class Shop extends Model
{
    /** @use HasFactory<ShopFactory> */
    use HasFactory, HasPublicSlug;

    protected $fillable = [
        'vendor_id',
        'name',
        'slug',
        'description',
        'logo',
        'cover_image',
        'phone',
        'email',
        'location',
        'country',
        'province',
        'city',
        'commune',
        'quarter',
        'address_line',
        'latitude',
        'longitude',
        'opening_hours',
        'timezone',
        'weekly_hours',
        'publish_location',
        'publish_address',
        'status',
        'address_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ShopStatus::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'publish_location' => 'boolean',
            'publish_address' => 'boolean',
            'weekly_hours' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(Review::class, Product::class);
    }

    public function mediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Storage::disk(config('twende.media.disk'))->url($path);
    }

    public function isPublic(): bool
    {
        return $this->status === ShopStatus::Active;
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function publicAddress(): ?string
    {
        if (! $this->publish_address) {
            return $this->location;
        }

        $parts = array_filter([
            $this->address_line,
            $this->quarter,
            $this->commune,
            $this->city,
            $this->province,
            $this->country,
        ]);

        if ($parts === []) {
            return $this->location;
        }

        return implode(', ', $parts);
    }

    /**
     * @return array{open: bool, label: string}|null
     */
    public function openState(): ?array
    {
        $hours = $this->weekly_hours;

        if (! is_array($hours) || $hours === []) {
            return null;
        }

        $now = now($this->timezone ?: config('app.timezone'));
        $key = strtolower($now->format('D'));
        $slot = $hours[$key] ?? null;
        $current = $now->format('H:i');

        if (! is_array($slot) || count($slot) < 2 || ! is_string($slot[0]) || ! is_string($slot[1])) {
            return ['open' => false, 'label' => __('experience.closed')];
        }

        $open = $current >= $slot[0] && $current < $slot[1];

        return [
            'open' => $open,
            'label' => $open
                ? __('experience.closes_at', ['time' => $slot[1]])
                : __('experience.closed'),
        ];
    }
}
