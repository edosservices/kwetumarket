<?php

namespace App\Models;

use App\Enums\CatalogStatus;
use App\Models\Concerns\HasPublicSlug;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, HasPublicSlug;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status' => CatalogStatus::class,
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return Storage::disk(config('twende.media.disk'))->url($this->image);
    }

    /**
     * @return array<int, int>
     */
    public static function idsWithDescendants(int $id): array
    {
        $rows = static::query()->get(['id', 'parent_id']);
        $ids = [$id];
        $growing = true;

        while ($growing) {
            $growing = false;

            foreach ($rows as $row) {
                if ($row->parent_id !== null && in_array((int) $row->parent_id, $ids, true) && ! in_array((int) $row->id, $ids, true)) {
                    $ids[] = (int) $row->id;
                    $growing = true;
                }
            }
        }

        return $ids;
    }

    /**
     * @return array<int, int>
     */
    public function descendantIds(): array
    {
        return array_values(array_filter(
            static::idsWithDescendants((int) $this->id),
            fn (int $id): bool => $id !== (int) $this->id,
        ));
    }
}
