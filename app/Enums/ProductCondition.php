<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum ProductCondition: string
{
    use HasValues;

    case New = 'new';
    case Used = 'used';
    case Refurbished = 'refurbished';
}
