<?php

namespace App\Services\Commerce;

use App\Models\PlatformSetting;
use App\Models\PointsTransaction;
use App\Models\PointsWallet;
use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PointsService
{
    public function __construct(private ExchangeRateService $rates) {}

    public function balance(User $user): int
    {
        return (int) (PointsWallet::query()->where('user_id', $user->id)->value('balance') ?? 0);
    }

    public function level(int $balance): string
    {
        $platinum = PlatformSetting::integer('level_platinum', (int) config('twende.points.levels.platinum'));
        $gold = PlatformSetting::integer('level_gold', (int) config('twende.points.levels.gold'));
        $silver = PlatformSetting::integer('level_silver', (int) config('twende.points.levels.silver'));

        return match (true) {
            $balance >= $platinum => 'platinum',
            $balance >= $gold => 'gold',
            $balance >= $silver => 'silver',
            default => 'bronze',
        };
    }

    public function perReferral(): int
    {
        return max(0, PlatformSetting::integer('points_per_referral', (int) config('twende.points.per_referral')));
    }

    /**
     * @return array{points: int, usd_cents: int}
     */
    public function coverUsdCents(User $user, int $usdCents): array
    {
        $needed = $this->rates->usdCentsToPoints($usdCents);
        $usable = min($this->balance($user), $needed, $this->dailyRemaining($user));
        $minimum = PlatformSetting::integer('points_min_conversion', (int) config('twende.points.min_conversion'));

        if ($usable < 1 || ($usable < $needed && $usable < $minimum)) {
            return ['points' => 0, 'usd_cents' => 0];
        }

        $covered = min($usdCents, $this->rates->pointsToUsdCents($usable));
        $points = $this->rates->usdCentsToPoints($covered);

        return ['points' => $points, 'usd_cents' => $covered];
    }

    public function award(User $user, int $points, string $type, ?string $note = null): void
    {
        if ($points < 1) {
            return;
        }

        DB::transaction(function () use ($user, $points, $type, $note): void {
            $wallet = PointsWallet::query()->firstOrCreate(['user_id' => $user->id], ['balance' => 0]);
            $wallet = PointsWallet::query()->whereKey($wallet->id)->lockForUpdate()->firstOrFail();
            $wallet->increment('balance', $points);
            $wallet->transactions()->create([
                'points' => $points,
                'type' => $type,
                'note' => $note,
            ]);
        });
    }

    public function redeem(User $user, int $points, string $note): void
    {
        if ($points < 1) {
            return;
        }

        DB::transaction(function () use ($user, $points, $note): void {
            $wallet = PointsWallet::query()->where('user_id', $user->id)->lockForUpdate()->first();

            if (! $wallet || (int) $wallet->balance < $points) {
                throw ValidationException::withMessages(['points' => __('experience.points_short')]);
            }

            $spentToday = (int) PointsTransaction::query()
                ->where('points_wallet_id', $wallet->id)
                ->where('points', '<', 0)
                ->where('created_at', '>=', now()->startOfDay())
                ->sum('points');
            $max = PlatformSetting::integer('points_max_daily', (int) config('twende.points.max_daily'));

            if (abs($spentToday) + $points > $max) {
                throw ValidationException::withMessages(['points' => __('experience.points_daily')]);
            }

            $wallet->decrement('balance', $points);
            $wallet->transactions()->create([
                'points' => -$points,
                'type' => 'redeem',
                'note' => $note,
            ]);
        });
    }

    public function reverseOrderReward(int $orderId): void
    {
        DB::transaction(function () use ($orderId): void {
            $reward = ReferralReward::query()->where('order_id', $orderId)->lockForUpdate()->first();

            if (! $reward || $reward->reversed_at) {
                return;
            }

            $wallet = PointsWallet::query()->where('user_id', $reward->user_id)->lockForUpdate()->first();
            $take = min((int) ($wallet->balance ?? 0), (int) $reward->points);

            if ($wallet && $take > 0) {
                $wallet->decrement('balance', $take);
                $wallet->transactions()->create([
                    'points' => -$take,
                    'type' => 'reversal',
                    'note' => 'order:'.$orderId,
                ]);
            }

            $reward->update(['reversed_at' => now()]);
        });
    }

    private function dailyRemaining(User $user): int
    {
        $walletId = PointsWallet::query()->where('user_id', $user->id)->value('id');
        $max = PlatformSetting::integer('points_max_daily', (int) config('twende.points.max_daily'));

        if (! $walletId) {
            return $max;
        }

        $spent = abs((int) PointsTransaction::query()
            ->where('points_wallet_id', $walletId)
            ->where('points', '<', 0)
            ->where('created_at', '>=', now()->startOfDay())
            ->sum('points'));

        return max(0, $max - $spent);
    }
}
