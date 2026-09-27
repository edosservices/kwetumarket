<x-layouts.storefront :title="$shop->name" :description="$shop->description">
    @php
        $rating = $shop->reviews_avg_rating;
        $stars = $rating !== null ? (int) round((float) $rating) : null;
        $verified = (bool) ($shop->vendor?->relationLoaded('certifications') ? $shop->vendor->certifications->isNotEmpty() : $shop->vendor?->isCertified());
        $place = collect([$shop->city, $shop->country])->filter()->implode(', ') ?: $shop->location;
    @endphp
    <section class="border-b border-twende-line bg-white dark:border-white/10 dark:bg-twende-night">
        <div class="mx-auto max-w-[100rem]">
            <div class="flex h-36 items-center justify-center bg-twende-light sm:h-48 dark:bg-white/5">
                @if ($shop->cover_image)
                    <img src="{{ $shop->mediaUrl($shop->cover_image) }}" alt="" class="h-full w-full object-cover">
                @else
                    <x-icon name="shop" class="h-12 w-12 text-twende-green" />
                @endif
            </div>
            <div class="flex flex-col gap-4 px-3 py-4 sm:flex-row sm:px-4">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-white ring-1 ring-twende-line dark:bg-twende-night dark:ring-white/10">
                    @if ($shop->logo)
                        <img src="{{ $shop->mediaUrl($shop->logo) }}" alt="" class="h-full w-full object-contain">
                    @else
                        <x-icon name="shop" class="h-7 w-7 text-twende-green" />
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-bold">{{ $shop->name }}</h1>
                        @if ($verified)
                            <span class="text-xs font-semibold text-twende-green">✓ {{ __('ui.store.verified_seller') }}</span>
                        @endif
                    </div>
                    @if ($stars !== null)
                        <p class="mt-1 text-sm">
                            @for ($i = 1; $i <= 5; $i++)
                                <span class="{{ $i <= $stars ? 'text-twende-green' : 'text-twende-line' }}">★</span>
                            @endfor
                            {{ number_format((float) $rating, 1, ',', ' ') }}
                        </p>
                    @endif
                    @if ($place)
                        <p class="mt-1 text-sm text-twende-muted">{{ $place }}</p>
                    @endif
                    <p class="mt-1 text-sm text-twende-muted">
                        {{ trans_choice('ui.store.product_count', (int) $shop->products_count, ['count' => (int) $shop->products_count]) }}
                        · {{ trans_choice('ui.store.order_count', (int) ($shop->sales_count ?? 0), ['count' => (int) ($shop->sales_count ?? 0)]) }}
                    </p>
                    @if ($shop->description)
                        <p class="mt-2 max-w-3xl text-sm leading-relaxed text-twende-muted">{{ $shop->description }}</p>
                    @endif
                    @if ($state = $shop->openState())
                        <p class="mt-2 text-sm font-semibold {{ $state['open'] ? 'text-twende-green' : 'text-twende-red' }}">{{ $state['label'] }}</p>
                    @endif
                    <div class="mt-3 flex flex-wrap gap-2" id="contacter">
                        <x-contact-links :links="$contacts" />
                        @auth
                            <form method="POST" action="{{ route($following ? 'follows.destroy' : 'follows.store', $shop) }}">
                                @csrf
                                @if ($following) @method('DELETE') @endif
                                <button class="inline-flex h-10 items-center rounded-full border border-twende-line px-4 text-sm font-semibold dark:border-white/15">{{ $following ? __('ui.store.following') : __('commerce.follow') }}</button>
                            </form>
                            @can('messages.create')
                            <form method="POST" action="{{ route('messages.store') }}" class="flex min-w-0 flex-1 gap-2">
                                @csrf
                                <input type="hidden" name="shop_id" value="{{ $shop->id }}">
                                <label class="sr-only" for="shop-contact">{{ __('ui.store.contact_vendor') }}</label>
                                <input id="shop-contact" name="body" required placeholder="{{ __('ui.store.contact_vendor') }}" class="h-10 min-w-0 flex-1 rounded-full border border-twende-line px-4 text-sm dark:border-white/15 dark:bg-twende-night">
                                <button class="h-10 shrink-0 rounded-full bg-twende-red px-4 text-sm font-semibold text-white">{{ __('commerce.write') }}</button>
                            </form>
                            @endcan
                        @else
                            <a href="{{ route('login') }}" class="inline-flex h-10 items-center rounded-full border border-twende-line px-4 text-sm font-semibold dark:border-white/15">{{ __('ui.store.contact_vendor') }}</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>
    @if ($shop->publish_location && $shop->hasCoordinates())
        <div class="mx-auto max-w-[100rem] px-3 pt-4 sm:px-4">
            <x-shop-map :markers="[['lat' => (float) $shop->latitude, 'lng' => (float) $shop->longitude, 'name' => $shop->name, 'nearest' => false]]" />
        </div>
    @endif
    <x-catalog-browser
        :title="__('ui.catalog.products_title')"
        :query="$query"
        :results="$results"
        :filters="$filters"
        :categories="$categories"
        :brands="$brands"
        :shops="collect()"
        :cities="collect()"
        :action="route('shops.show', $shop)"
        :show-query="true"
        hide-shop
    />
</x-layouts.storefront>
