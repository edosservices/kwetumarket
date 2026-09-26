<?php

namespace App\Console\Commands;

use App\Models\Cart;
use App\Notifications\CommerceNotice;
use Illuminate\Console\Command;

class RemindAbandonedCarts extends Command
{
    protected $signature = 'twende:carts-abandoned';

    protected $description = 'Remind customers once about carts left with items';

    public function handle(): int
    {
        $carts = Cart::query()
            ->whereNull('abandoned_reminded_at')
            ->whereNotNull('user_id')
            ->where('updated_at', '<=', now()->subHours(2))
            ->whereHas('items')
            ->with('user')
            ->limit(100)
            ->get();

        foreach ($carts as $cart) {
            $cart->user?->notify(new CommerceNotice(
                __('experience.cart_reminder_title'),
                __('experience.cart_reminder_body'),
                route('cart.show'),
            ));
            $cart->update(['abandoned_reminded_at' => now()]);
        }

        $this->info($carts->count().' carts reminded');

        return self::SUCCESS;
    }
}
