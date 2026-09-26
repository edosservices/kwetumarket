<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdCampaign extends Model
{
    protected $fillable = ['vendor_id', 'title', 'body', 'status', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query
            ->where('status', 'approved')
            ->where(function (Builder $inner): void {
                $inner->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $inner): void {
                $inner->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }
}
