<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = ['name', 'slug', 'price', 'price_usd_cents', 'interval_days', 'is_active'];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'price_usd_cents' => 'integer',
            'interval_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(VendorSubscription::class);
    }
}
