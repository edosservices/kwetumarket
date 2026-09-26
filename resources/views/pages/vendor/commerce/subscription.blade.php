<x-layouts.dashboard :title="__('commerce.subscription')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('commerce.subscription') }}</h1>
    <p class="mt-2 text-sm text-twende-muted">{{ __('commerce.sandbox_note') }}</p>
    <p class="mt-4 text-sm font-semibold">{{ __('experience.points', ['count' => number_format($points, 0, ',', ' ')]) }} · {{ __('experience.level', ['level' => __('experience.levels.'.$level)]) }}</p>
    @if ($rate)
        <p class="mt-2 text-sm text-twende-muted">{{ __('experience.rate_line', ['amount' => \App\Support\Money::format((int) $rate->minor_per_unit, $rate->quote), 'date' => $rate->quoted_at->timezone(config('app.timezone'))->format('d/m/Y')]) }}</p>
    @else
        <p class="mt-2 text-sm text-twende-red">{{ __('experience.rate_missing') }}</p>
    @endif
    @if ($current)
        <p class="mt-4 font-semibold">{{ $current->plan->name }} · {{ $current->status }} · {{ $current->ends_at->format('d/m/Y') }}</p>
    @endif
    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        @foreach ($plans as $plan)
            @php
                $usd = (int) ($plan->price_usd_cents ?? 0);
                $cdf = $usd > 0 && $rate ? app(\App\Services\Commerce\ExchangeRateService::class)->usdCentsToQuoteMinor($usd) : null;
            @endphp
            <form method="POST" action="{{ route('vendor.subscription.store') }}" class="rounded-2xl border border-twende-line p-4 dark:border-white/10">
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                <h2 class="font-semibold">{{ $plan->name }}</h2>
                @if ($usd > 0)
                    <p class="mt-2">{{ $cdf !== null ? __('experience.subscription_usd', ['usd' => \App\Support\Money::format($usd, 'USD'), 'cdf' => \App\Support\Money::format($cdf, $rate->quote)]) : __('experience.rate_missing') }}</p>
                @else
                    <p class="mt-2">{{ \App\Support\Money::format((int) $plan->price) }}</p>
                @endif
                <p class="text-sm text-twende-muted">{{ $plan->interval_days }} j</p>
                @if ($usd > 0)
                    <label class="mt-3 flex items-center gap-2 text-sm"><input type="checkbox" name="use_points" value="1"> {{ __('experience.use_points') }}</label>
                @endif
                <button class="mt-4 h-10 rounded-full bg-twende-red px-4 text-sm font-semibold text-white" @disabled($usd > 0 && ! $rate)>{{ __('commerce.subscription') }}</button>
            </form>
        @endforeach
    </div>
</x-layouts.dashboard>
