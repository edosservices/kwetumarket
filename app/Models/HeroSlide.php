<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HeroSlide extends Model
{
    protected $fillable = [
        'placement',
        'title',
        'subtitle',
        'cta_label',
        'cta_url',
        'image',
        'sort_order',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function scopeVisible(Builder $query, string $placement): Builder
    {
        return $query
            ->where('placement', $placement)
            ->where('is_active', true)
            ->where(function (Builder $window): void {
                $window->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $window): void {
                $window->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return Storage::disk(config('twende.media.disk'))->url($this->image);
    }
}
