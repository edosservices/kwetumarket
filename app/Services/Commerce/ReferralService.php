<?php

namespace App\Services\Commerce;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Referral;
use App\Models\User;
use App\Notifications\CommerceNotice;

class ReferralService
{
    public function attach(User $user, ?string $code): void
    {
        $code = strtoupper(trim((string) $code));

        if ($code === '' || $user->referral_code === $code) {
            return;
        }

        $referrer = User::query()->where('referral_code', $code)->first();

        if (! $referrer || (int) $referrer->id === (int) $user->id) {
            return;
        }

        Referral::query()->firstOrCreate(
            ['referred_id' => $user->id],
            ['referrer_id' => $referrer->id, 'code' => $code],
        );
    }

    public function rewardFirstPaidOrder(User $buyer, Order $order): void
    {
        if ($order->payment_status !== 'paid') {
            return;
        }

        $referral = Referral::query()
            ->where('referred_id', $buyer->id)
            ->whereNull('rewarded_at')
            ->first();

        if (! $referral) {
            return;
        }

        $paidBefore = Order::query()
            ->where('user_id', $buyer->id)
            ->where('payment_status', 'paid')
            ->whereKeyNot($order->id)
            ->exists();

        if ($paidBefore) {
            return;
        }

        $amount = max(0, (int) config('twende.commerce.referral_reward'));

        if ($amount < 1) {
            $referral->update(['rewarded_at' => now()]);

            return;
        }

        $code = 'PARRAIN-'.$referral->id;
        Coupon::query()->firstOrCreate(
            ['code' => $code],
            [
                'type' => 'fixed',
                'value' => $amount,
                'min_subtotal' => 0,
                'is_active' => true,
                'usage_limit' => 1,
                'used_count' => 0,
            ],
        );

        $referral->update(['rewarded_at' => now()]);
        $referral->referrer?->notify(new CommerceNotice(
            __('commerce.referral_reward_title'),
            __('commerce.referral_reward_body', ['code' => $code]),
            route('cart.show'),
        ));
    }
}
