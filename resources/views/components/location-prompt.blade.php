<div {{ $attributes->class('rounded-3xl border border-twende-line bg-twende-light p-5 dark:border-white/10 dark:bg-white/5') }} x-data="{ denied: false }">
    <p class="text-sm leading-relaxed">{{ __('ui.smart.location_explainer') }}</p>
    <div class="mt-4 flex flex-wrap gap-2">
        <button type="button" class="inline-flex h-11 items-center rounded-full bg-twende-green px-4 text-sm font-semibold text-white" x-on:click="if (!navigator.geolocation) { denied = true; return; } navigator.geolocation.getCurrentPosition((position) => { const url = new URL(window.location.href); url.searchParams.set('lat', position.coords.latitude.toFixed(4)); url.searchParams.set('lng', position.coords.longitude.toFixed(4)); window.location = url.toString(); }, () => { denied = true; })">
            {{ __('ui.smart.allow_location') }}
        </button>
        <button type="button" class="inline-flex h-11 items-center rounded-full border border-twende-line bg-white px-4 text-sm font-semibold dark:border-white/15 dark:bg-transparent" x-on:click="denied = true">
            {{ __('ui.smart.continue_without') }}
        </button>
    </div>
    <p class="mt-3 text-sm text-twende-muted" x-show="denied" x-cloak>{{ __('ui.smart.location_disabled') }}</p>
</div>
