@php
    $markers = collect($offers)
        ->filter(fn ($offer) => $offer->product->shop->publish_location && $offer->product->shop->hasCoordinates())
        ->unique(fn ($offer) => $offer->product->shop_id)
        ->map(fn ($offer) => [
            'lat' => (float) $offer->product->shop->latitude,
            'lng' => (float) $offer->product->shop->longitude,
            'name' => $offer->product->shop->name,
            'nearest' => $offer->nearest,
        ])
        ->values();
@endphp

<x-layouts.storefront :title="__('ui.smart.promotions_title')">
    <section class="mx-auto max-w-7xl px-4 py-8">
        <h1 class="text-3xl font-bold">{{ __('ui.smart.promotions_title') }}</h1>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-twende-muted">
            {{ $located ? __('ui.smart.nearby_promotions', ['radius' => $radius]) : __('ui.smart.promotions_intro') }}
        </p>

        @unless ($located)
            <div class="mt-6">
                <x-location-prompt />
            </div>
        @else
            <form method="GET" class="mt-6 flex flex-wrap items-end gap-3">
                <input type="hidden" name="lat" value="{{ $latitude }}">
                <input type="hidden" name="lng" value="{{ $longitude }}">
                <label class="text-sm font-medium">
                    {{ __('ui.smart.radius') }}
                    <select name="radius" class="mt-1 block h-11 rounded-xl border border-twende-line bg-white px-3 dark:border-white/15 dark:bg-twende-night">
                        @foreach (config('twende.nearby.radii_km') as $option)
                            <option value="{{ $option }}" @selected(abs((float) $option - (float) $radius) < 0.001)>{{ $option < 1 ? ((int) ($option * 1000)).' m' : $option.' km' }}</option>
                        @endforeach
                    </select>
                </label>
                <x-button type="submit" size="sm">{{ __('ui.smart.apply') }}</x-button>
            </form>
        @endif

        <div class="mt-8">
            @if ($offers === [])
                <x-empty-state :title="__('ui.smart.no_promotions')" />
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($offers as $offer)
                        <x-smart-offer :offer="$offer" />
                    @endforeach
                </div>
            @endif
        </div>

        @if ($located)
            <div class="mt-10">
                <x-shop-map :markers="$markers" :latitude="$latitude" :longitude="$longitude" />
            </div>
        @endif
    </section>
</x-layouts.storefront>
