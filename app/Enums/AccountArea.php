<?php

namespace App\Enums;

use App\Models\User;

enum AccountArea: string
{
    case Admin = 'admin';
    case Vendor = 'vendor';
    case Delivery = 'delivery';
    case Customer = 'customer';

    /**
     * @return list<UserRole>
     */
    public function roles(): array
    {
        return match ($this) {
            self::Admin => UserRole::platform(),
            self::Vendor => UserRole::vendorSide(),
            self::Delivery => UserRole::deliverySide(),
            self::Customer => [UserRole::Customer],
        };
    }

    public function middleware(): string
    {
        return implode('|', array_map(fn (UserRole $role) => $role->value, $this->roles()));
    }

    public function layout(): string
    {
        return 'layouts.'.$this->value;
    }

    public function homeRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Vendor => 'vendor.dashboard',
            self::Delivery => 'delivery.dashboard',
            self::Customer => 'customer.dashboard',
        };
    }

    public static function for(User $user): self
    {
        foreach (self::cases() as $area) {
            if ($user->hasAnyRole(array_map(fn (UserRole $role) => $role->value, $area->roles()))) {
                return $area;
            }
        }

        return self::Customer;
    }
}
