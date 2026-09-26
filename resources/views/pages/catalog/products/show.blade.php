<x-layouts.storefront :title="$product->name" :description="\Illuminate\Support\Str::limit(strip_tags($product->description), 160)">
    <article class="mx-auto grid max-w-7xl gap-8 px-4 py-8 lg:grid-cols-2">
        <div>
            <div class="flex aspect-square items-center justify-center overflow-hidden rounded-3xl bg-twende-light dark:bg-white/5">
                @if ($product->primaryImage)
                    <img src="{{ $product->primaryImage->url() }}" alt="{{ $product->primaryImage->alt_text }}" class="h-full w-full object-contain">
                @elseif ($product->images->first())
                    <img src="{{ $product->images->first()->url() }}" alt="{{ $product->images->first()->alt_text }}" class="h-full w-full object-contain">
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
            <div class="mt-6 flex flex-col gap-3">
                <x-contact-links :links="$contacts" />
                <button type="button" disabled class="inline-flex h-12 w-fit cursor-not-allowed items-center justify-center rounded-full bg-twende-light px-6 text-sm font-semibold text-twende-muted dark:bg-white/10">
                    {{ __('ui.catalog.cart_later') }}
                </button>
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
        </div>
    </article>
</x-layouts.storefront>
