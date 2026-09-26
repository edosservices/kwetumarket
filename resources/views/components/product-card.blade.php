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
    $href = $href ?? ($product ? route('products.show', $product) : route('products.index'));
    $needsVariant = $product && ($product->relationLoaded('variants') ? $product->variants->isNotEmpty() : (bool) ($product->has_variants ?? false));
    $ratingValue = $rating ?? ($product ? $product->reviews_avg_rating : null);
    $certified = (bool) ($product?->shop?->vendor?->relationLoaded('certifications') ? $product->shop->vendor->certifications->isNotEmpty() : $product?->shop?->vendor?->isCertified());
@endphp

<article {{ $attributes->class('group flex h-full flex-col overflow-hidden rounded-2xl border border-twende-line bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md motion-safe:hover:scale-[1.02] dark:border-white/10 dark:bg-twende-night-card') }}>
    <a href="{{ $href }}" class="flex flex-1 flex-col">
        <div class="flex aspect-square items-center justify-center bg-twende-light dark:bg-white/5">
            @if ($image)
                <img src="{{ $image }}" alt="{{ $name }}" class="h-full w-full object-contain" loading="lazy">
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
            @if ($certified)
                <p class="text-xs font-semibold text-twende-green">{{ __('commerce.certified') }}</p>
            @endif
            @if ($product?->is_dropship)
                <p class="text-xs font-semibold text-twende-muted">{{ __('commerce.dropship') }}</p>
            @endif
            @if (! is_null($ratingValue))
                <p class="text-sm text-twende-muted">{{ __('ui.catalog.rating', ['rating' => number_format((float) $ratingValue, 1, ',', ' ')]) }}</p>
            @endif
            <p class="mt-auto text-base font-bold text-twende-red">
                {{ $price }} <span class="text-xs font-semibold">{{ $currency }}</span>
            </p>
            @if ($comparePrice)
                <p class="text-sm text-twende-muted line-through">{{ $comparePrice }} {{ $currency }}</p>
            @endif
        </div>
    </a>
    @if ($showCart && $product)
        <div class="px-4 pb-4">
            @if ($needsVariant || ! $available)
                <a href="{{ route('products.show', $product) }}#acheter" class="inline-flex h-10 w-full items-center justify-center rounded-full bg-twende-green-bright px-4 text-sm font-semibold text-white">{{ $needsVariant ? __('commerce.choose_variant') : __('commerce.add') }}</a>
            @else
                <form method="POST" action="{{ route('cart.items.store') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button type="submit" class="inline-flex h-10 w-full items-center justify-center rounded-full bg-twende-green-bright px-4 text-sm font-semibold text-white hover:bg-twende-green">{{ __('commerce.add') }}</button>
                </form>
            @endif
        </div>
    @endif
</article>
