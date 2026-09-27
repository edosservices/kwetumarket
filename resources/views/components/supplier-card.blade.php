@props(['shop'])

@php
    $rating = $shop->reviews_avg_rating;
    $stars = $rating !== null ? (int) round((float) $rating) : null;
    $verified = (bool) ($shop->vendor?->relationLoaded('certifications') ? $shop->vendor->certifications->isNotEmpty() : $shop->vendor?->isCertified());
    $place = collect([$shop->city, $shop->country ?: ($shop->city ? 'RDC' : null)])->filter()->implode(', ') ?: $shop->location;
@endphp

<article {{ $attributes->class('flex h-full flex-col rounded-lg border border-twende-line bg-white p-3 dark:border-white/10 dark:bg-twende-night-card') }}>
    <div class="flex gap-3">
        <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-twende-light dark:bg-white/5">
            @if ($shop->logo)
                <img src="{{ $shop->mediaUrl($shop->logo) }}" alt="" class="h-full w-full object-contain">
            @else
                <x-icon name="shop" class="h-6 w-6 text-twende-green" />
            @endif
        </div>
        <div class="min-w-0">
            <h3 class="truncate text-sm font-bold">{{ $shop->name }}</h3>
            @if ($verified)
                <p class="text-xs font-semibold text-twende-green">✓ {{ __('ui.store.verified_seller') }}</p>
            @endif
            @if ($stars !== null)
                <p class="text-xs">
                    @for ($i = 1; $i <= 5; $i++)
                        <span class="{{ $i <= $stars ? 'text-twende-green' : 'text-twende-line' }}">★</span>
                    @endfor
                    {{ number_format((float) $rating, 1, ',', ' ') }}
                </p>
            @endif
            @if ($place)
                <p class="truncate text-xs text-twende-muted">{{ $place }}</p>
            @endif
        </div>
    </div>
    <dl class="mt-3 grid grid-cols-2 gap-2 text-xs">
        <div>
            <dt class="text-twende-muted">{{ __('ui.nav.products') }}</dt>
            <dd class="font-semibold">{{ trans_choice('ui.store.product_count', (int) $shop->products_count, ['count' => (int) $shop->products_count]) }}</dd>
        </div>
        <div>
            <dt class="text-twende-muted">{{ __('commerce.orders') }}</dt>
            <dd class="font-semibold">{{ trans_choice('ui.store.order_count', (int) ($shop->sales_count ?? 0), ['count' => (int) ($shop->sales_count ?? 0)]) }}</dd>
        </div>
    </dl>
    <div class="mt-3 flex flex-wrap gap-2">
        <a href="{{ route('shops.show', $shop) }}" class="inline-flex h-8 items-center rounded-full bg-twende-red px-3 text-xs font-semibold text-white">{{ __('ui.store.visit_shop') }}</a>
        <a href="{{ route('shops.show', $shop) }}#contacter" class="inline-flex h-8 items-center rounded-full border border-twende-line px-3 text-xs font-semibold dark:border-white/15">{{ __('ui.store.contact_vendor') }}</a>
    </div>
</article>
