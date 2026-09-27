<x-layouts.storefront :title="$product->name" :description="\Illuminate\Support\Str::limit(strip_tags($product->description), 160)">
    @php
        $fallbackImage = $product->primaryImage?->url() ?? $product->images->first()?->url();
        $variantPayload = $product->variants->map(fn ($variant) => [
            'id' => $variant->id,
            'name' => $variant->name,
            'color' => $variant->colorLabel(),
            'hex' => $variant->color_hex,
            'size' => $variant->sizeLabel(),
            'stock' => (int) $variant->stock,
            'price' => $variant->formattedSalePrice(),
            'compare' => $variant->salePrice() < $variant->effectivePrice() ? $variant->formattedPrice() : null,
            'image' => $variant->image?->url(),
        ])->values();
        $initialVariant = $product->variants->first(fn ($variant) => $variant->stock > 0) ?? $product->variants->first();
    @endphp
    <article
        class="mx-auto grid max-w-7xl gap-8 px-4 py-8 lg:grid-cols-2"
        x-data="{
            variants: {{ \Illuminate\Support\Js::from($variantPayload) }},
            selected: {{ $initialVariant?->id ?? 'null' }},
            current() { return this.variants.find((variant) => variant.id === this.selected) || null; },
            choose(id) { this.selected = id; },
            stockText() {
                const variant = this.current();
                if (! variant) return '';
                if (variant.stock < 1) return @js(__('experience.out_of_stock'));
                if (variant.stock <= 3) return @js(__('experience.low_stock', ['count' => '__COUNT__'])).replace('__COUNT__', variant.stock);
                return @js(__('experience.in_stock'));
            },
        }"
    >
        <div>
            <div class="flex aspect-square items-center justify-center overflow-hidden rounded-3xl bg-twende-light dark:bg-white/5">
                @if ($fallbackImage)
                    <img x-bind:src="current() && current().image ? current().image : @js($fallbackImage)" src="{{ $fallbackImage }}" alt="{{ $product->name }}" class="h-full w-full object-contain">
                @else
                    <x-icon name="bag" class="h-16 w-16 text-twende-green" />
                @endif
            </div>
            @if ($product->images->count() > 1)
                <div class="mt-3 flex gap-2 overflow-x-auto">
                    @foreach ($product->images as $image)
                        <img src="{{ $image->url() }}" alt="{{ $image->alt_text }}" class="h-16 w-16 shrink-0 rounded-xl border border-twende-line object-contain dark:border-white/10">
                    @endforeach
                </div>
            @endif
        </div>
        <div>
            <p class="text-sm font-semibold text-twende-green">
                <a href="{{ route('shops.show', $product->shop) }}">{{ $product->shop->name }}</a>
            </p>
            <h1 class="mt-2 text-3xl font-bold">{{ $product->name }}</h1>
            <p class="mt-2 text-sm text-twende-muted">{{ $product->category->name }} @if ($product->brand) · {{ $product->brand->name }} @endif</p>
            <div class="mt-4 flex flex-wrap items-end gap-3">
                <p class="text-3xl font-bold text-twende-red">{{ \App\Support\Money::amount($pricing->finalPrice) }} <span class="text-base">{{ $product->currency }}</span></p>
                @if ($pricing->comparePrice)
                    <p class="text-lg text-twende-muted line-through">{{ \App\Support\Money::amount($pricing->comparePrice) }} {{ $product->currency }}</p>
                @endif
                @if ($pricing->discountPercent)
                    <x-badge variant="green">-{{ $pricing->discountPercent }}%</x-badge>
                @endif
            </div>
            @if ($pricing->savings)
                <p class="mt-2 text-sm font-semibold text-twende-green">{{ __('ui.smart.savings', ['amount' => \App\Support\Money::format($pricing->savings, $product->currency)]) }}</p>
            @endif
            <div class="mt-4 flex flex-wrap gap-2">
                <x-badge :variant="$product->availableQuantity() > 0 ? 'green' : 'neutral'">
                    {{ $stockLabel['label'] }}
                </x-badge>
                <x-badge>{{ __('ui.catalog.conditions.'.$product->condition->value) }}</x-badge>
                <x-badge>{{ __('ui.catalog.sku') }} {{ $product->sku }}</x-badge>
            </div>
            <div class="mt-6 flex flex-col gap-3" id="acheter">
                <x-contact-links :links="$contacts" />
                <form id="add-to-cart" method="POST" action="{{ route('cart.items.store') }}" class="grid gap-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    @if ($product->variants->isNotEmpty())
                        @php
                            $colors = $product->variants->filter(fn ($variant) => $variant->colorLabel())->unique(fn ($variant) => $variant->colorLabel())->values();
                            $sizes = $product->variants->filter(fn ($variant) => $variant->sizeLabel())->unique(fn ($variant) => $variant->sizeLabel())->values();
                        @endphp
                        @if ($colors->isNotEmpty())
                            <div>
                                <p class="text-sm font-semibold">{{ __('experience.color') }}</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($colors as $color)
                                        <button type="button" class="inline-flex items-center gap-2 rounded-full border border-twende-line px-3 py-2 text-sm dark:border-white/15" x-on:click="const match = variants.find((variant) => variant.color === @js($color->colorLabel()) && variant.size === current()?.size && variant.stock > 0) || variants.find((variant) => variant.color === @js($color->colorLabel()) && variant.stock > 0) || variants.find((variant) => variant.color === @js($color->colorLabel())); if (match) selected = match.id">
                                            @if ($color->color_hex)
                                                <span class="h-4 w-4 rounded-full border border-black/10" style="background: {{ $color->color_hex }}"></span>
                                            @endif
                                            {{ $color->colorLabel() }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if ($sizes->isNotEmpty())
                            <div>
                                <p class="text-sm font-semibold">{{ __('experience.size') }}</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($sizes as $size)
                                        <button type="button" class="h-10 rounded-full border border-twende-line px-4 text-sm font-semibold dark:border-white/15" x-on:click="const match = variants.find((variant) => variant.size === @js($size->sizeLabel()) && variant.color === current()?.color && variant.stock > 0) || variants.find((variant) => variant.size === @js($size->sizeLabel()) && variant.stock > 0) || variants.find((variant) => variant.size === @js($size->sizeLabel())); if (match) selected = match.id">{{ $size->sizeLabel() }}</button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <fieldset class="grid gap-2">
                            <legend class="text-sm font-semibold">{{ __('ui.catalog.variants') }}</legend>
                            <p class="text-sm font-semibold" x-text="stockText()"></p>
                            <p class="text-sm" x-show="current()" x-text="current() ? current().price + ' {{ $product->currency }}' : ''"></p>
                            @foreach ($product->variants as $variant)
                                <label class="flex items-center justify-between gap-3 rounded-2xl border border-twende-line px-3 py-3 text-sm dark:border-white/10">
                                    <span class="flex items-center gap-2">
                                        <input type="radio" name="product_variant_id" value="{{ $variant->id }}" @checked($initialVariant && (int) $variant->id === (int) $initialVariant->id) x-bind:checked="selected === {{ $variant->id }}" x-on:change="selected = {{ $variant->id }}" @disabled($variant->stock < 1)>
                                        @if ($variant->color_hex)
                                            <span class="h-4 w-4 rounded-full border border-black/10" style="background: {{ $variant->color_hex }}"></span>
                                        @endif
                                        {{ $variant->name }}
                                    </span>
                                    <span>{{ $variant->formattedSalePrice() }} {{ $product->currency }} · {{ $variant->stock }}</span>
                                </label>
                            @endforeach
                        </fieldset>
                    @endif
                    @if ($product->shop->city)
                        <p class="text-sm text-twende-green"><x-icon name="pin" class="inline h-4 w-4 text-twende-green" /> {{ __('experience.ship_from', ['city' => $product->shop->city]) }}</p>
                    @endif
                    @if ($deliveryFrom)
                        <p class="text-sm"><x-icon name="truck" class="inline h-4 w-4 text-twende-green" /> {{ __('experience.delivery_from', ['fee' => $deliveryFrom->formattedFee()]) }}</p>
                    @endif
                    <label class="text-sm">{{ __('commerce.quantity') }}
                        <input name="quantity" type="number" min="1" value="1" class="mt-1 h-11 w-24 rounded-xl border border-twende-line px-3 dark:border-white/15 dark:bg-twende-night">
                    </label>
                    <button type="submit" class="inline-flex h-12 w-full items-center justify-center rounded-full bg-twende-green-bright px-6 text-sm font-semibold text-white hover:bg-twende-green sm:w-fit">{{ __('commerce.add') }}</button>
                </form>
                <div class="flex flex-wrap gap-2">
                    @auth
                        <form method="POST" action="{{ route($favorite ? 'favorites.destroy' : 'favorites.store', $product) }}">
                            @csrf
                            @if ($favorite) @method('DELETE') @endif
                            <button class="h-10 rounded-full border border-twende-line px-4 text-sm font-semibold dark:border-white/15">{{ $favorite ? __('commerce.favorite_removed') : __('commerce.favorite') }}</button>
                        </form>
                        <form method="POST" action="{{ route('messages.store') }}" class="flex min-w-0 flex-1 gap-2">
                            @csrf
                            <input type="hidden" name="shop_id" value="{{ $product->shop_id }}">
                            <label class="sr-only" for="shop-message">{{ __('commerce.contact_shop') }}</label>
                            <input id="shop-message" name="body" required placeholder="{{ __('commerce.contact_shop') }}" class="h-10 min-w-0 flex-1 rounded-full border border-twende-line px-4 text-sm dark:border-white/15 dark:bg-twende-night">
                            <button class="h-10 shrink-0 rounded-full bg-twende-red px-4 text-sm font-semibold text-white">{{ __('commerce.write') }}</button>
                        </form>
                    @endauth
                </div>
                @if ($product->is_dropship)
                    <p class="text-sm text-twende-muted">{{ __('commerce.dropship') }}@if($product->supplier_name) — {{ $product->supplier_name }}@endif</p>
                @endif
            </div>
            <div class="prose mt-8 max-w-none text-sm leading-relaxed text-twende-dark dark:text-white">
                {!! nl2br(e($product->description)) !!}
            </div>
            <section class="mt-8">
                <h2 class="text-lg font-semibold">{{ __('ui.catalog.variants') }}</h2>
                @if ($product->variants->isEmpty())
                    <p class="mt-2 text-sm text-twende-muted">{{ __('ui.catalog.no_variants') }}</p>
                @else
                    <ul class="mt-3 divide-y divide-twende-line rounded-2xl border border-twende-line dark:divide-white/10 dark:border-white/10">
                        @foreach ($product->variants as $variant)
                            <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                                <span class="font-medium">{{ $variant->name }}</span>
                                <span class="text-twende-muted">{{ $variant->sku }}</span>
                                <span>{{ $variant->formattedPrice() }} {{ $product->currency }}</span>
                                <x-badge :variant="$variant->stock > 0 ? 'green' : 'neutral'">{{ $variant->stock }}</x-badge>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
            <section class="mt-8">
                <h2 class="text-lg font-semibold">{{ __('commerce.reviews') }}</h2>
                @forelse ($reviews as $review)
                    <article class="mt-3 rounded-2xl border border-twende-line p-4 text-sm dark:border-white/10">
                        <p class="font-semibold">{{ $review->user?->name }} · {{ $review->rating }}/5</p>
                        <p class="mt-1">{{ $review->body }}</p>
                        @if ($review->vendor_reply)
                            <p class="mt-2 text-twende-muted">{{ __('operations.vendor_reply') }} — {{ $review->vendor_reply }}</p>
                        @endif
                        @if ($review->relationLoaded('photos') || $review->photos)
                            <div class="mt-2 flex gap-2">
                                @foreach ($review->photos as $photo)
                                    <img src="{{ $photo->url() }}" alt="" class="h-16 w-16 rounded-xl object-cover" loading="lazy">
                                @endforeach
                            </div>
                        @endif
                    </article>
                @empty
                    <p class="mt-2 text-sm text-twende-muted">{{ __('commerce.no_reviews') }}</p>
                @endforelse
            </section>
        </div>
    </article>
    @if ($similar->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pb-28">
            <h2 class="text-xl font-bold">{{ __('commerce.similar') }}</h2>
            <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
                @foreach ($similar as $item)
                    <x-product-card :product="$item" />
                @endforeach
            </div>
        </section>
    @endif
    <div class="fixed inset-x-0 bottom-20 z-30 border-t border-twende-line bg-white p-3 lg:hidden dark:border-white/10 dark:bg-twende-night">
        <button type="submit" form="add-to-cart" class="inline-flex h-12 w-full items-center justify-center rounded-full bg-twende-green-bright text-sm font-semibold text-white">{{ __('commerce.sticky_cart') }}</button>
    </div>
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'sku' => $product->sku,
            'description' => \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 300),
            'image' => $fallbackImage,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
</x-layouts.storefront>
