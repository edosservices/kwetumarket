<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointsTransaction extends Model
{
    protected $fillable = ['points_wallet_id', 'points', 'type', 'note'];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(PointsWallet::class, 'points_wallet_id');
    }
}
