@props([
    'product' => null,
    'name' => null,
    'price' => null,
    'currency' => null,
    'href' => null,
    'shop' => null,
    'image' => null,
    'badge' => null,
    'comparePrice' => null,
    'discount' => null,
    'rating' => null,
    'available' => null,
    'showCart' => true,
])

@php
    if ($product) {
        $name = $name ?? $product->name;
        $price = $price ?? $product->formattedPrice();
        $currency = $currency ?? $product->currency;
        $href = $href ?? route('products.show', $product);
        $shop = $shop ?? $product->shop?->name;
        $image = $image ?? $product->primaryImage?->url();
        $comparePrice = $comparePrice ?? $product->formattedComparePrice();
        $discount = $discount ?? $product->discountPercent();
        $available = $available ?? ($product->availableQuantity() > 0);
        if ($badge === null && $product->condition?->value !== 'new') {
            $badge = __('ui.catalog.conditions.'.$product->condition->value);
        }
    }
    $currency = $currency ?? 'CDF';
    $href = $href ?? '#';
@endphp

<article {{ $attributes->class('group flex h-full flex-col overflow-hidden rounded-2xl border border-twende-line bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-twende-night-card') }}>
    <a href="{{ $href }}" class="flex flex-1 flex-col">
        <div class="flex aspect-square items-center justify-center bg-twende-light dark:bg-white/5">
            @if ($image)
                <img src="{{ $image }}" alt="" class="h-full w-full object-contain" loading="lazy">
            @else
                <x-icon name="bag" class="h-10 w-10 text-twende-green" />
            @endif
        </div>
        <div class="flex flex-1 flex-col gap-2 p-4">
            <div class="flex flex-wrap gap-2">
                @if ($badge)
                    <x-badge variant="red">{{ $badge }}</x-badge>
                @endif
                @if ($discount)
                    <x-badge variant="green">-{{ $discount }}%</x-badge>
                @endif
                @if (! is_null($available))
                    <x-badge :variant="$available ? 'green' : 'neutral'">{{ $available ? __('ui.catalog.available') : __('ui.catalog.unavailable') }}</x-badge>
                @endif
            </div>
            <h3 class="line-clamp-2 font-semibold text-twende-dark group-hover:text-twende-red dark:text-white">{{ $name }}</h3>
            @if ($shop)
                <p class="text-sm text-twende-muted">{{ $shop }}</p>
            @endif
            @if (! is_null($rating))
                <p class="text-sm text-twende-muted">{{ __('ui.catalog.rating', ['rating' => $rating]) }}</p>
            @endif
            <p class="mt-auto text-base font-bold text-twende-red">
                {{ $price }} <span class="text-xs font-semibold">{{ $currency }}</span>
            </p>
            @if ($comparePrice)
                <p class="text-sm text-twende-muted line-through">{{ $comparePrice }} {{ $currency }}</p>
            @endif
        </div>
    </a>
    @if ($showCart)
        <div class="px-4 pb-4">
            <button type="button" disabled class="inline-flex h-10 w-full cursor-not-allowed items-center justify-center rounded-full bg-twende-light px-4 text-sm font-semibold text-twende-muted dark:bg-white/10">
                {{ __('ui.catalog.cart_later') }}
            </button>
        </div>
    @endif
</article>
