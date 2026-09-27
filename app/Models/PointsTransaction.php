<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointsTransaction extends Model
{
    protected $fillable = ['points_wallet_id', 'points', 'remaining', 'type', 'note', 'expires_at'];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'remaining' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(PointsWallet::class, 'points_wallet_id');
    }
}
