<?php

namespace App\Services\Commerce;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Referral;
use App\Models\ReferralReward;
use App\Models\User;
use App\Notifications\CommerceNotice;

class ReferralService
{
    public function __construct(private PointsService $points) {}
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

        if (! $referral || $referral->blocked_at) {
            return;
        }

        if ($this->sameHousehold($buyer, $referral)) {
            $referral->update(['blocked_at' => now()]);

            return;
        }

        $firstPaid = Order::query()
            ->where('user_id', $buyer->id)
            ->where('payment_status', 'paid')
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('id')
            ->first();

        if (! $firstPaid || (int) $firstPaid->id !== (int) $order->id || ! $this->qualified($buyer)) {
            return;
        }

        $referral->update(['rewarded_at' => now()]);
        $rewardPoints = $this->points->perReferral();

        if ($rewardPoints > 0 && $referral->referrer) {
            $this->points->award($referral->referrer, $rewardPoints, 'referral', $order->number);
            ReferralReward::query()->create([
                'referral_id' => $referral->id,
                'user_id' => $referral->referrer_id,
                'order_id' => $order->id,
                'points' => $rewardPoints,
            ]);
        }

        $amount = max(0, (int) config('twende.commerce.referral_reward'));
        $code = null;

        if ($amount > 0) {
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
        }

        $referral->referrer?->notify(new CommerceNotice(
            __('commerce.referral_reward_title'),
            $code
                ? __('commerce.referral_reward_body', ['code' => $code])
                : __('experience.points_awarded', ['points' => $rewardPoints]),
            route('referral'),
        ));
    }

    public function reverseReward(Order $order): void
    {
        $this->points->reverseOrderReward($order->id);
    }

    private function qualified(User $buyer): bool
    {
        if ($buyer->email_verified_at === null) {
            return false;
        }

        if (filter_var(config('twende.points.require_phone'), FILTER_VALIDATE_BOOL) && $buyer->phone_verified_at === null) {
            return false;
        }

        return true;
    }

    private function sameHousehold(User $buyer, Referral $referral): bool
    {
        $referrer = $referral->referrer;

        if (! $referrer) {
            return true;
        }

        if ((int) $referrer->id === (int) $buyer->id) {
            return true;
        }

        return $buyer->phone && $referrer->phone && $buyer->phone === $referrer->phone;
    }
}
