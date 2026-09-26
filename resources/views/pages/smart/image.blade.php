@php
    $analysis = $insight->label
        ?: ($insight->color
            ? __('ui.smart.dominant_color', ['color' => __('ui.smart.colors.'.$insight->color)])
            : __('ui.smart.no_identification'));
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

<x-layouts.storefront :title="__('ui.smart.image_title')">
    <section class="mx-auto max-w-7xl px-4 py-8">
        <p class="text-sm font-semibold text-twende-green">{{ __('ui.smart.name') }}</p>
        <h1 class="mt-1 text-3xl font-bold">{{ __('ui.smart.image_title') }}</h1>

        @if ($search->limited)
            <x-alert class="mt-4">{{ __('ui.smart.limited') }}</x-alert>
        @endif

        <div class="mt-6 grid gap-4 rounded-3xl border border-twende-line bg-white p-4 dark:border-white/10 dark:bg-twende-night-card sm:grid-cols-[160px_1fr] sm:p-5">
            <img src="{{ route('search.image.file', $search) }}" alt="{{ __('ui.smart.preview_alt') }}" class="h-40 w-full rounded-2xl bg-twende-light object-contain dark:bg-white/5">
            <div>
                <p class="text-sm font-semibold text-twende-muted">{{ __('ui.smart.analysis') }}</p>
                <p class="mt-1 text-xl font-semibold">{{ $analysis }}</p>
                @if ($insight->brand || $insight->category)
                    <p class="mt-2 text-sm text-twende-muted">
                        {{ collect([$insight->brand, $insight->category, $insight->model])->filter()->implode(' · ') }}
                    </p>
                @endif
            </div>
        </div>

        @unless ($located)
            <div class="mt-6">
                <x-location-prompt />
            </div>
        @endunless

        <div class="mt-6 flex flex-wrap gap-2">
            @foreach (['match', 'nearest', 'price', 'stock', 'promotion', 'popular'] as $option)
                <a href="{{ request()->fullUrlWithQuery(['sort' => $option]) }}" class="inline-flex h-9 items-center rounded-full px-3 text-sm font-semibold {{ $sort === $option ? 'bg-twende-red text-white' : 'bg-twende-light text-twende-dark dark:bg-white/10 dark:text-white' }}">
                    {{ __('ui.smart.sorts.'.$option) }}
                </a>
            @endforeach
        </div>

        <h2 class="mt-8 text-xl font-bold">{{ __('ui.smart.products_found') }}</h2>
        @if ($offers === [])
            <x-empty-state class="mt-4" :title="__('ui.smart.no_matches')" />
        @else
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($offers as $offer)
                    <x-smart-offer :offer="$offer" show-match />
                @endforeach
            </div>
        @endif

        <section class="mt-10">
            <h2 class="text-xl font-bold">{{ __('ui.smart.nearest_shop') }}</h2>
            @if ($nearest)
                <div class="mt-4 rounded-3xl border border-twende-green/40 bg-twende-green/5 p-5">
                    <p class="text-sm font-semibold text-twende-green">{{ $nearest->product->shop->name }}</p>
                    <h3 class="mt-1 text-2xl font-bold">{{ $nearest->product->name }}</h3>
                    <dl class="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                        @if ($nearest->distanceLabel)
                            <div><dt class="text-twende-muted">{{ __('ui.smart.distance') }}</dt><dd class="font-semibold">{{ $nearest->distanceLabel }}</dd></div>
                        @endif
                        <div><dt class="text-twende-muted">{{ __('ui.smart.stock') }}</dt><dd class="font-semibold">{{ $nearest->stockLabel }}</dd></div>
                        <div><dt class="text-twende-muted">{{ __('ui.smart.price') }}</dt><dd class="font-semibold text-twende-red">{{ $nearest->finalFormatted() }}</dd></div>
                        @if ($nearest->discountPercent)
                            <div><dt class="text-twende-muted">{{ __('ui.smart.promotion_badge') }}</dt><dd class="font-semibold">-{{ $nearest->discountPercent }} %</dd></div>
                        @endif
                        <div><dt class="text-twende-muted">{{ __('ui.smart.visual_match') }}</dt><dd class="font-semibold">{{ __('ui.smart.match_percent', ['percent' => $nearest->matchPercent]) }}</dd></div>
                    </dl>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <x-button :href="route('shops.show', $nearest->product->shop)" size="sm" variant="secondary">{{ __('ui.smart.view_shop') }}</x-button>
                        <x-contact-links :links="app(\App\Services\Catalog\VendorContacts::class)->forShop($nearest->product->shop, $nearest->product->name)" />
                    </div>
                </div>
            @else
                <p class="mt-3 text-sm text-twende-muted">{{ $located ? __('ui.smart.no_nearest') : __('ui.smart.location_disabled') }}</p>
            @endif
        </section>

        @if (count($offers) > 1)
            <section class="mt-10">
                <h2 class="text-xl font-bold">{{ __('ui.smart.other_shops') }}</h2>
                <ul class="mt-4 divide-y divide-twende-line rounded-3xl border border-twende-line dark:divide-white/10 dark:border-white/10">
                    @foreach ($offers as $offer)
                        @continue($offer->nearest)
                        <li class="flex flex-col gap-2 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold">{{ $offer->product->shop->name }}</p>
                                <p class="text-sm text-twende-muted">{{ $offer->product->name }} · {{ $offer->finalFormatted() }} · {{ $offer->stockLabel }} @if ($offer->distanceLabel) · {{ $offer->distanceLabel }} @endif</p>
                            </div>
                            <a href="{{ route('shops.show', $offer->product->shop) }}" class="text-sm font-semibold text-twende-green">{{ __('ui.smart.view_shop') }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($located)
            <section class="mt-10">
                <h2 class="text-xl font-bold">{{ __('ui.smart.nearby_promotions', ['radius' => $radius]) }}</h2>
                @if ($promotions === [])
                    <p class="mt-3 text-sm text-twende-muted">{{ __('ui.smart.no_promotions') }}</p>
                @else
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($promotions as $offer)
                            <x-smart-offer :offer="$offer" />
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        <div class="mt-10">
            <x-shop-map :markers="$markers" :latitude="$latitude" :longitude="$longitude" />
        </div>
    </section>
</x-layouts.storefront>
