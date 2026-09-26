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
    $nearest = collect($offers)->first(fn ($offer) => $offer->nearest);
@endphp

<x-layouts.storefront :title="__('ui.smart.nearby')">
    <section class="mx-auto max-w-7xl px-4 py-8">
        <h1 class="text-3xl font-bold">{{ __('ui.smart.nearby') }}</h1>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-twende-muted">{{ __('ui.smart.nearby_intro') }}</p>

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
                <input type="hidden" name="sort" value="{{ $sort }}">
                <x-button type="submit" size="sm">{{ __('ui.smart.apply') }}</x-button>
            </form>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach (['nearest', 'price', 'stock', 'promotion', 'popular'] as $option)
                    <a href="{{ request()->fullUrlWithQuery(['sort' => $option]) }}" class="inline-flex h-9 items-center rounded-full px-3 text-sm font-semibold {{ $sort === $option ? 'bg-twende-red text-white' : 'bg-twende-light text-twende-dark dark:bg-white/10 dark:text-white' }}">
                        {{ __('ui.smart.sorts.'.$option) }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($located && $nearest)
            <section class="mt-8 rounded-3xl border border-twende-green/40 bg-twende-green/5 p-5">
                <h2 class="text-lg font-bold">{{ __('ui.smart.nearest_shop') }}</h2>
                <p class="mt-2 text-xl font-semibold">{{ $nearest->product->shop->name }}</p>
                <p class="mt-1 text-sm text-twende-muted">{{ $nearest->distanceLabel }} · {{ $nearest->stockLabel }} · {{ $nearest->finalFormatted() }}</p>
                <div class="mt-4">
                    <x-button :href="route('shops.show', $nearest->product->shop)" size="sm" variant="secondary">{{ __('ui.smart.view_shop') }}</x-button>
                </div>
            </section>
        @endif

        <div class="mt-8">
            @if (! $located)
                <x-empty-state :title="__('ui.smart.location_disabled')" />
            @elseif ($offers === [])
                <x-empty-state :title="__('ui.smart.no_nearby')" />
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
