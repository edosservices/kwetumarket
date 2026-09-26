<?php

namespace App\Services\Commerce;

use App\Models\ExchangeRate;
use App\Models\PlatformSetting;

class ExchangeRateService
{
    public function latest(string $base = 'USD', string $quote = 'CDF'): ?ExchangeRate
    {
        return ExchangeRate::query()
            ->where('base', $base)
            ->where('quote', $quote)
            ->orderByDesc('quoted_at')
            ->orderByDesc('id')
            ->first();
    }

    public function usdCentsToQuoteMinor(int $usdCents, string $quote = 'CDF'): ?int
    {
        $rate = $this->latest('USD', $quote);

        if (! $rate || $usdCents < 0 || (int) $rate->minor_per_unit < 1) {
            return null;
        }

        return intdiv($usdCents * (int) $rate->minor_per_unit, 100);
    }

    public function pointsPerUsd(): int
    {
        return max(1, PlatformSetting::integer('points_per_usd', (int) config('twende.points.per_usd')));
    }

    public function pointsToUsdCents(int $points): int
    {
        if ($points < 1) {
            return 0;
        }

        return intdiv($points * 100, $this->pointsPerUsd());
    }

    public function usdCentsToPoints(int $usdCents): int
    {
        if ($usdCents < 1) {
            return 0;
        }

        return intdiv($usdCents * $this->pointsPerUsd(), 100);
    }
}
