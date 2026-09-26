<x-layouts.dashboard :title="__('commerce.settings')">
    <h1 class="text-2xl font-bold">{{ __('commerce.settings') }}</h1>
    <p class="mt-3 max-w-2xl text-sm text-twende-muted">{{ __('commerce.settings_note') }}</p>
    <dl class="mt-6 max-w-xl divide-y divide-twende-line rounded-2xl border border-twende-line dark:divide-white/10 dark:border-white/10">
        @foreach ($settings as $key => $value)
            <div class="flex justify-between gap-3 px-4 py-3 text-sm"><dt class="font-medium">{{ $key }}</dt><dd>{{ $value }}</dd></div>
        @endforeach
    </dl>
    <h2 class="mt-8 text-lg font-semibold">{{ __('experience.marketing_settings') }}</h2>
    <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-4 grid max-w-xl gap-3">
        @csrf
        @method('PUT')
        <x-input name="points_per_referral" label="points_per_referral" type="number" :value="old('points_per_referral', $points['points_per_referral'])" />
        <x-input name="points_per_usd" label="points_per_usd" type="number" :value="old('points_per_usd', $points['points_per_usd'])" />
        <x-input name="points_min_conversion" label="points_min_conversion" type="number" :value="old('points_min_conversion', $points['points_min_conversion'])" />
        <x-input name="points_max_daily" label="points_max_daily" type="number" :value="old('points_max_daily', $points['points_max_daily'])" />
        <x-input name="free_shipping_minor" label="free_shipping_minor" type="number" :value="old('free_shipping_minor', $points['free_shipping_minor'])" />
        <x-input name="minor_per_unit" label="USD → CDF minor" type="number" :value="old('minor_per_unit', $rate?->minor_per_unit)" />
        <button class="h-11 w-fit rounded-full bg-twende-red px-5 text-sm font-semibold text-white">{{ __('ui.catalog.save') }}</button>
    </form>
</x-layouts.dashboard>
