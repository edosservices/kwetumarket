<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierProfile extends Model
{
    public const OFFLINE = 'offline';

    public const AVAILABLE = 'available';

    public const BUSY = 'busy';

    public const ON_DELIVERY = 'on_delivery';

    public const PAUSED = 'paused';

    protected $fillable = [
        'user_id', 'vehicle_type', 'vehicle_plate', 'availability', 'document_disk', 'document_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function canAcceptJobs(): bool
    {
        return $this->availability === self::AVAILABLE;
    }
}
