<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;

class BrandPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Brand $brand): bool
    {
        if ($brand->status->value === 'active') {
            return true;
        }

        return $user?->can('products.manage') === true;
    }

    public function create(User $user): bool
    {
        return $user->can('products.manage');
    }

    public function update(User $user, Brand $brand): bool
    {
        return $user->can('products.manage');
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $user->can('products.manage');
    }
}
