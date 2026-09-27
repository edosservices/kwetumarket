<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function view(User $user, Product $product): bool
    {
        if (! $user->can('products.view')) {
            return false;
        }

        if ($user->isVendorSide()) {
            return $product->vendor_id === $user->vendorId();
        }

        return $user->isPlatformStaff();
    }

    public function update(User $user, Product $product): bool
    {
        if (! $user->can('products.edit') || ! $this->view($user, $product)) {
            return false;
        }

        return $user->isVendorSide() || $user->isPlatformStaff();
    }

    public function create(User $user): bool
    {
        return $user->can('products.create') && $user->isVendorSide() && $user->vendorId() !== null;
    }
}
