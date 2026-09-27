<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        if (! $user->can('orders.view')) {
            return false;
        }

        if ($user->area()->value === 'customer') {
            return (int) $order->user_id === (int) $user->id;
        }

        if ($user->isVendorSide()) {
            return $order->items()->where('vendor_id', $user->vendorId())->exists();
        }

        return $user->isPlatformStaff();
    }
}
