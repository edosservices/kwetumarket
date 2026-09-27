<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Login;

class SyncUserLocale
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        $available = config('twende.locales', []);
        $sessionLocale = session('locale');

        if (is_string($sessionLocale) && in_array($sessionLocale, $available, true)) {
            if ($user->locale !== $sessionLocale) {
                $user->forceFill(['locale' => $sessionLocale])->save();
            }

            return;
        }

        if (is_string($user->locale) && in_array($user->locale, $available, true)) {
            session(['locale' => $user->locale]);
        }
    }
}
