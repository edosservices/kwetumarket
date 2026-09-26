<?php

namespace App\Policies;

use App\Enums\VendorStatus;
use App\Models\Shop;
use App\Models\User;

class ShopPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Shop $shop): bool
    {
        if ($shop->isPublic()) {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($user->can('shops.manage')) {
            return true;
        }

        return $this->owns($user, $shop);
    }

    public function create(User $user): bool
    {
        if ($user->can('shops.manage')) {
            return true;
        }

        return $user->can('shops.manage-own') && $this->canOperate($user);
    }

    public function update(User $user, Shop $shop): bool
    {
        if ($user->can('shops.manage')) {
            return true;
        }

        return $user->can('shops.manage-own') && $this->canOperate($user) && $this->owns($user, $shop);
    }

    public function delete(User $user, Shop $shop): bool
    {
        return $user->can('shops.manage');
    }

    private function owns(User $user, Shop $shop): bool
    {
        $shop->loadMissing('vendor');

        return (int) $shop->vendor?->user_id === (int) $user->id;
    }

    private function canOperate(User $user): bool
    {
        $user->loadMissing('vendorProfile');

        return in_array($user->vendorProfile?->status, [VendorStatus::Active, VendorStatus::Pending], true);
    }
}
