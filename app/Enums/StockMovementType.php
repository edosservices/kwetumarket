<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum StockMovementType: string
{
    use HasValues;

    case Purchase = 'purchase';
    case Sale = 'sale';
    case Return = 'return';
    case Adjustment = 'adjustment';
    case Reservation = 'reservation';
    case Release = 'release';
    case Cancellation = 'cancellation';
}
