<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Delivery;
use App\Models\User;

class DeliveryPolicy
{
    public function view(User $user, Delivery $delivery): bool
    {
        if (! $user->can('delivery.view')) {
            return false;
        }

        if ($user->hasRole(UserRole::DeliveryAgent->value) && ! $user->hasRole(UserRole::DeliveryManager->value) && ! $user->isPlatformStaff()) {
            return (int) $delivery->agent_id === (int) $user->id;
        }

        return $user->hasRole(UserRole::DeliveryManager->value) || $user->isPlatformStaff();
    }

    public function update(User $user, Delivery $delivery): bool
    {
        return $user->can('delivery.update') && $this->view($user, $delivery);
    }

    public function assign(User $user, Delivery $delivery): bool
    {
        return $user->can('delivery.assign')
            && ($user->hasRole(UserRole::DeliveryManager->value) || $user->isPlatformStaff());
    }
}
