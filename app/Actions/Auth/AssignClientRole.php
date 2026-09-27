<?php

namespace App\Actions\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Events\Registered;

class AssignClientRole
{
    public function handle(Registered $event): void
    {
        $user = $event->user;

        if (! $user instanceof User || $user->roles()->exists()) {
            return;
        }

        $user->assignRole(UserRole::Client->value);
    }
}
