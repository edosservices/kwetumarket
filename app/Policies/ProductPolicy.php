<?php

namespace App\Policies;

use App\Enums\VendorStatus;
use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Product $product): bool
    {
        if ($product->isPubliclyVisible()) {
            return true;
        }

        if (! $user) {
            return false;
        }

        if ($user->can('products.manage')) {
            return true;
        }

        return $this->managesOwn($user, $product);
    }

    public function create(User $user): bool
    {
        if ($user->can('products.manage')) {
            return true;
        }

        return $user->can('products.manage-own') && $this->canOperate($user);
    }

    public function update(User $user, Product $product): bool
    {
        if ($user->can('products.manage')) {
            return true;
        }

        return $user->can('products.manage-own')
            && $this->canOperate($user)
            && $this->managesOwn($user, $product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }

    private function managesOwn(User $user, Product $product): bool
    {
        $product->loadMissing('shop.vendor');

        return (int) $product->shop?->vendor?->user_id === (int) $user->id;
    }

    private function canOperate(User $user): bool
    {
        $user->loadMissing('vendorProfile');

        return in_array($user->vendorProfile?->status, [VendorStatus::Active, VendorStatus::Pending], true);
    }
}
