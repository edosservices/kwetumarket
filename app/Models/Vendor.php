<?php

namespace App\Models;

use App\Enums\VendorStatus;
use Database\Factories\VendorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    /** @use HasFactory<VendorFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'manager_name',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => VendorStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shops(): HasMany
    {
        return $this->hasMany(Shop::class);
    }

    public function socialLinks(): HasMany
    {
        return $this->hasMany(VendorSocialLink::class);
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(VendorCertification::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(VendorSubscription::class);
    }

    public function ads(): HasMany
    {
        return $this->hasMany(AdCampaign::class);
    }

    public function isCertified(): bool
    {
        if ($this->relationLoaded('certifications')) {
            return $this->certifications->contains(fn ($item) => $item->status === 'approved');
        }

        return $this->certifications()->where('status', 'approved')->exists();
    }

    public function isActive(): bool
    {
        return $this->status === VendorStatus::Active;
    }
}
