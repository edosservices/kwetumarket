<?php

namespace App\Models;

use App\Enums\ShopStatus;
use App\Models\Concerns\HasPublicSlug;
use Database\Factories\ShopFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'status',
        'address_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ShopStatus::class,
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
}
