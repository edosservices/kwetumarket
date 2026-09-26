<?php

namespace App\Models;

use App\Data\ImageInsight;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ImageSearch extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'disk',
        'image_path',
        'provider',
        'limited',
        'label',
        'detected_category',
        'detected_brand',
        'detected_text',
        'detected_attributes',
        'confidence',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'limited' => 'boolean',
            'detected_attributes' => 'array',
            'confidence' => 'float',
            'expires_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function insight(): ImageInsight
    {
        $attributes = $this->detected_attributes ?? [];

        return new ImageInsight(
            provider: (string) $this->provider,
            limited: (bool) $this->limited,
            label: $this->label,
            category: $this->detected_category,
            subcategory: $attributes['subcategory'] ?? null,
            brand: $this->detected_brand,
            model: $attributes['model'] ?? null,
            color: $attributes['color'] ?? null,
            shape: $attributes['shape'] ?? null,
            visibleText: $this->detected_text,
            keywords: array_values($attributes['keywords'] ?? []),
            attributes: array_values($attributes['traits'] ?? []),
            confidence: (float) $this->confidence,
        );
    }

    public function absolutePath(): string
    {
        return Storage::disk($this->disk)->path($this->image_path);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
