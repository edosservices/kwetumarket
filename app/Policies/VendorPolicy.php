<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vendor;

class VendorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('vendors.manage');
    }

    public function view(User $user, Vendor $vendor): bool
    {
        return $user->can('vendors.manage') || (int) $vendor->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('vendors.manage') || $user->can('shops.manage-own');
    }

    public function update(User $user, Vendor $vendor): bool
    {
        if ($user->can('vendors.manage')) {
            return true;
        }

        return $user->can('shops.manage-own') && (int) $vendor->user_id === (int) $user->id;
    }
}
