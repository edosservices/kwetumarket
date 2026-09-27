<?php

namespace App\Enums;

enum UserRole: string
{
    case Client = 'client';
    case Vendor = 'vendor';
    case DeliveryAgent = 'delivery_agent';
    case Admin = 'admin';

    public function label(): string
    {
        return __('ui.roles.'.$this->value);
    }
}
