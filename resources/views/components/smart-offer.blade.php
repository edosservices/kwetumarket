@props([
    'offer',
    'showMatch' => false,
])

@php
    $product = $offer->product;
    $image = $product->primaryImage;
@endphp

<article {{ $attributes->class('flex h-full flex-col overflow-hidden rounded-3xl border border-twende-line bg-white dark:border-white/10 dark:bg-twende-night-card') }}>
    <a href="{{ route('products.show', $product) }}" class="flex aspect-square items-center justify-center bg-twende-light dark:bg-white/5">
        @if ($image)
            <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?: $product->name }}" class="h-full w-full object-contain">
        @else
            <x-icon name="bag" class="h-12 w-12 text-twende-green" />
        @endif
    </a>
    <div class="flex flex-1 flex-col gap-3 p-4">
        <div class="flex flex-wrap gap-2">
            @if ($showMatch)
                <x-badge>{{ __('ui.smart.match_kinds.'.$offer->matchKind) }}</x-badge>
                <x-badge variant="green">{{ __('ui.smart.match_percent', ['percent' => $offer->matchPercent]) }}</x-badge>
            @endif
            @if ($offer->discountPercent)
                <x-badge variant="red">{{ __('ui.smart.promotion_badge') }} -{{ $offer->discountPercent }} %</x-badge>
            @endif
        </div>
        <h3 class="text-base font-semibold leading-snug">
            <a href="{{ route('products.show', $product) }}" class="hover:text-twende-red">{{ $product->name }}</a>
        </h3>
        <div>
            @if ($offer->compareFormatted())
                <p class="text-sm text-twende-muted line-through">{{ $offer->compareFormatted() }}</p>
            @endif
            <p class="text-xl font-bold text-twende-red">{{ $offer->finalFormatted() }}</p>
            @if ($offer->savingsFormatted())
                <p class="text-sm text-twende-green">{{ __('ui.smart.savings', ['amount' => $offer->savingsFormatted()]) }}</p>
            @endif
            @if ($offer->promotionEndsAt)
                <p class="text-xs text-twende-muted">{{ __('ui.smart.promotion_until', ['date' => $offer->promotionEndsAt]) }}</p>
            @endif
        </div>
        <p class="text-sm {{ $offer->stockTone === 'out' ? 'text-twende-red' : 'text-twende-dark dark:text-white' }}">{{ $offer->stockLabel }}</p>
        <p class="text-sm">
            <a href="{{ route('shops.show', $product->shop) }}" class="font-semibold text-twende-green hover:underline">{{ $product->shop->name }}</a>
            @if ($offer->distanceLabel)
                <span class="text-twende-muted"> · {{ $offer->distanceLabel }}</span>
            @endif
        </p>
        @if ($product->shop->publish_address && $product->shop->publicAddress())
            <p class="text-xs text-twende-muted">{{ $product->shop->publicAddress() }}</p>
        @endif
        <x-contact-links :links="app(\App\Services\Catalog\VendorContacts::class)->forShop($product->shop, $product->name)" />
        <div class="mt-auto flex flex-wrap gap-2 pt-1">
            <x-button :href="route('products.show', $product)" size="sm">{{ __('ui.smart.view_product') }}</x-button>
            <x-button :href="route('shops.show', $product->shop)" variant="outline" size="sm">{{ __('ui.smart.view_shop') }}</x-button>
        </div>
    </div>
</article>
