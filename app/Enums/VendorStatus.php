<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum VendorStatus: string
{
    use HasValues;

    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';
    case Blocked = 'blocked';
}
