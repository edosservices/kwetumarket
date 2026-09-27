<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(User $actor, User $user): bool
    {
        return $actor->is($user) || $actor->can('users.view');
    }

    public function update(User $actor, User $user): bool
    {
        return $actor->is($user) || $actor->can('users.edit');
    }

    public function suspend(User $actor, User $user): bool
    {
        if ($actor->is($user) || ! $actor->can('users.suspend')) {
            return false;
        }

        if ($user->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            return false;
        }

        return true;
    }
}
