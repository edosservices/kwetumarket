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
    $stars = $ratingValue !== null ? (int) round((float) $ratingValue) : null;
    $certified = (bool) ($product?->shop?->vendor?->relationLoaded('certifications') ? $product->shop->vendor->certifications->isNotEmpty() : $product?->shop?->vendor?->isCertified());
    $isNew = $product?->created_at && $product->created_at->greaterThan(now()->subDays(21));
    $isBestseller = (int) ($product->units_sold ?? 0) > 0;
@endphp

<article {{ $attributes->class('twende-lift group relative flex h-full flex-col overflow-hidden rounded-lg border border-twende-line bg-white dark:border-white/10 dark:bg-twende-night-card') }}>
    @if ($product)
        <div class="absolute right-1.5 top-1.5 z-10">
            @auth
                <form method="POST" action="{{ route('favorites.store', $product) }}">
                    @csrf
                    <button type="submit" class="twende-press inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/95 text-twende-red shadow-sm" aria-label="{{ __('commerce.favorite') }}">
                        <x-icon name="heart" class="h-4 w-4" />
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white/95 text-twende-red shadow-sm" aria-label="{{ __('commerce.favorite') }}">
                    <x-icon name="heart" class="h-4 w-4" />
                </a>
            @endauth
        </div>
    @endif
    <a href="{{ $href }}" class="flex min-h-0 flex-1 flex-col">
        <div class="flex aspect-[4/3] items-center justify-center bg-twende-light p-2 dark:bg-white/5">
            @if ($image)
                <img src="{{ $image }}" alt="{{ $name }}" class="h-full w-full object-contain" loading="lazy">
            @else
                <x-icon name="bag" class="h-8 w-8 text-twende-green" />
            @endif
        </div>
        <div class="flex flex-1 flex-col gap-1 px-2.5 py-2">
            <div class="flex flex-wrap gap-1">
                @if ($discount)
                    <span class="rounded bg-twende-red px-1.5 py-0.5 text-[10px] font-bold text-white">-{{ $discount }}%</span>
                @endif
                @if ($isBestseller)
                    <span class="rounded bg-twende-green px-1.5 py-0.5 text-[10px] font-bold text-white">{{ __('ui.store.bestseller_badge') }}</span>
                @elseif ($isNew)
                    <span class="rounded bg-twende-dark px-1.5 py-0.5 text-[10px] font-bold text-white">{{ __('ui.store.new_badge') }}</span>
                @endif
                @if ($badge)
                    <span class="rounded bg-twende-light px-1.5 py-0.5 text-[10px] font-semibold text-twende-dark">{{ $badge }}</span>
                @endif
            </div>
            <h3 class="line-clamp-2 text-sm font-medium leading-snug text-twende-dark group-hover:text-twende-red dark:text-white">{{ $name }}</h3>
            @if ($stars !== null)
                <p class="text-xs leading-none" aria-label="{{ __('ui.catalog.rating', ['rating' => number_format((float) $ratingValue, 1, ',', ' ')]) }}">
                    @for ($i = 1; $i <= 5; $i++)
                        <span class="{{ $i <= $stars ? 'text-twende-green' : 'text-twende-line' }}">★</span>
                    @endfor
                    <span class="text-twende-muted">{{ number_format((float) $ratingValue, 1, ',', ' ') }}</span>
                </p>
            @endif
            <p class="text-sm font-bold text-twende-dark dark:text-white">{{ $price }} <span class="text-[11px] font-semibold">{{ $currency }}</span></p>
            @if ($comparePrice)
                <p class="text-xs text-twende-muted line-through">{{ $comparePrice }} {{ $currency }}</p>
            @endif
            @if ($shop)
                <p class="truncate text-xs text-twende-muted">{{ $shop }}@if ($certified) · {{ __('commerce.certified') }}@endif</p>
            @endif
            @if (! is_null($available))
                <p class="text-[11px] font-semibold {{ $available ? 'text-twende-green' : 'text-twende-red' }}">{{ $available ? __('ui.catalog.available') : __('ui.catalog.unavailable') }}</p>
            @endif
        </div>
    </a>
    @if ($showCart && $product)
        <div class="twende-reveal px-2.5 pb-2.5 md:opacity-0 md:group-focus-within:opacity-100 md:group-hover:opacity-100">
            @if ($needsVariant || ! $available)
                <a href="{{ route('products.show', $product) }}#acheter" class="twende-press inline-flex h-8 w-full items-center justify-center rounded-md bg-twende-green-bright px-2 text-xs font-semibold text-white hover:bg-twende-green">{{ $needsVariant ? __('commerce.choose_variant') : __('commerce.add') }}</a>
            @else
                <form method="POST" action="{{ route('cart.items.store') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button type="submit" class="twende-press inline-flex h-8 w-full items-center justify-center rounded-md bg-twende-green-bright px-2 text-xs font-semibold text-white hover:bg-twende-green">{{ __('commerce.add') }}</button>
                </form>
            @endif
        </div>
    @endif
</article>
