<x-layouts.storefront :title="$shop->name" :description="$shop->description">
    <section class="mx-auto max-w-7xl px-4 py-8">
        <div class="overflow-hidden rounded-3xl border border-twende-line bg-white dark:border-white/10 dark:bg-twende-night-card">
            <div class="flex h-40 items-center justify-center bg-twende-light sm:h-56 dark:bg-white/5">
                @if ($shop->cover_image)
                    <img src="{{ $shop->mediaUrl($shop->cover_image) }}" alt="" class="h-full w-full object-cover">
                @else
                    <x-icon name="shop" class="h-12 w-12 text-twende-green" />
                @endif
            </div>
            <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white ring-1 ring-twende-line dark:bg-twende-night dark:ring-white/10">
                    @if ($shop->logo)
                        <img src="{{ $shop->mediaUrl($shop->logo) }}" alt="" class="h-full w-full object-contain">
                    @else
                        <x-icon name="shop" class="h-7 w-7 text-twende-green" />
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-3xl font-bold">{{ $shop->name }}</h1>
                        <x-badge :variant="$shop->isPublic() ? 'green' : 'neutral'">{{ __('ui.catalog.shop_statuses.'.$shop->status->value) }}</x-badge>
                    </div>
                    @if ($shop->location)
                        <p class="mt-2 text-sm text-twende-muted">{{ $shop->location }}</p>
                    @endif
                    @if ($shop->publish_address && $shop->publicAddress() && $shop->publicAddress() !== $shop->location)
                        <p class="mt-1 text-sm text-twende-muted">{{ $shop->publicAddress() }}</p>
                    @endif
                    @if ($shop->opening_hours)
                        <p class="mt-2 text-sm">{{ $shop->opening_hours }}</p>
                    @endif
                    @if ($state = $shop->openState())
                        <p class="mt-2 text-sm font-semibold {{ $state['open'] ? 'text-twende-green' : 'text-twende-red' }}">{{ $state['label'] }}</p>
                    @endif
                    @if ($shop->description)
                        <p class="mt-3 max-w-3xl text-sm leading-relaxed text-twende-muted">{{ $shop->description }}</p>
                    @endif
                    <dl class="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                        @if ($shop->phone)
                            <div><dt class="text-twende-muted">{{ __('ui.catalog.phone') }}</dt><dd class="font-medium">{{ $shop->phone }}</dd></div>
                        @endif
                        @if ($shop->email)
                            <div><dt class="text-twende-muted">{{ __('ui.catalog.email') }}</dt><dd class="font-medium">{{ $shop->email }}</dd></div>
                        @endif
                    </dl>
                    <div class="mt-4">
                        <x-contact-links :links="$contacts" />
                    </div>
                </div>
            </div>
        </div>
        @if ($shop->publish_location && $shop->hasCoordinates())
            <div class="mt-6">
                <x-shop-map :markers="[['lat' => (float) $shop->latitude, 'lng' => (float) $shop->longitude, 'name' => $shop->name, 'nearest' => false]]" />
            </div>
        @endif
        <h2 class="mt-10 text-xl font-bold">{{ __('ui.catalog.products_title') }}</h2>
        <p class="mt-2 text-sm text-twende-muted">{{ trans_choice('ui.search.results', $results['total'], ['count' => $results['total']]) }}</p>
        <div class="mt-6">
            @if ($results['total'] === 0)
                <x-empty-state :title="__('ui.catalog.empty')" />
            @else
                <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4 xl:grid-cols-4">
                    @foreach ($results['items'] as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
                <div class="mt-8">
                    <x-pagination :paginator="$results['paginator']" />
                </div>
            @endif
        </div>
    </section>
</x-layouts.storefront>
