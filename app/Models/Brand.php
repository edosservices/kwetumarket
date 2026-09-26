<?php

namespace App\Models;

use App\Enums\CatalogStatus;
use App\Models\Concerns\HasPublicSlug;
use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    /** @use HasFactory<BrandFactory> */
    use HasFactory, HasPublicSlug;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CatalogStatus::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function logoUrl(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        return Storage::disk(config('twende.media.disk'))->url($this->logo);
    }
}
