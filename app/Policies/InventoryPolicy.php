<?php

namespace App\Policies;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;

class InventoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('products.manage') || $user->can('inventory.manage-own');
    }

    public function view(User $user, Inventory $inventory): bool
    {
        return $this->updateProduct($user, $inventory->product);
    }

    public function create(User $user, Product $product): bool
    {
        return $this->updateProduct($user, $product);
    }

    private function updateProduct(User $user, Product $product): bool
    {
        return $user->can('update', $product);
    }
}
