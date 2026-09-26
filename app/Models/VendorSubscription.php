<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorSubscription extends Model
{
    protected $fillable = [
        'vendor_id',
        'subscription_plan_id',
        'status',
        'starts_at',
        'ends_at',
        'payment_reference',
        'amount_minor',
        'points_spent',
        'rate_minor_per_unit',
        'rate_quoted_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'amount_minor' => 'integer',
            'points_spent' => 'integer',
            'rate_minor_per_unit' => 'integer',
            'rate_quoted_at' => 'datetime',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }
}
