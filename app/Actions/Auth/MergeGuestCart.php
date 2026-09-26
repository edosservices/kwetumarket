<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\Commerce\CartService;
use Illuminate\Auth\Events\Login;

class MergeGuestCart
{
    public function __construct(private CartService $carts) {}

    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        $this->carts->adopt($user);
    }
}
