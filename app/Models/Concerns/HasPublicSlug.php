<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasPublicSlug
{
    public static function bootHasPublicSlug(): void
    {
        static::saving(function (self $model): void {
            if (filled($model->slug)) {
                return;
            }

            $source = (string) ($model->name ?? '');
            $model->slug = static::nextAvailableSlug($source, $model->getKey());
        });
    }

    public static function nextAvailableSlug(string $source, int|string|null $ignoreId = null): string
    {
        $base = Str::slug($source);
        $base = $base !== '' ? $base : 'element';
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
