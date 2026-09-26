<?php

namespace App\Models;

use App\Enums\SocialPlatform;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorSocialLink extends Model
{
    protected $fillable = [
        'vendor_id',
        'platform',
        'username',
        'url',
    ];

    protected function casts(): array
    {
        return [
            'platform' => SocialPlatform::class,
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
