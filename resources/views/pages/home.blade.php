<x-layouts.storefront :title="__('ui.home.title')" :description="__('ui.home.subtitle')">
    @if ($slides->isNotEmpty())
        <section aria-label="{{ __('ui.store.welcome') }}">
            <x-hero-carousel :slides="$slides" welcome />
        </section>
    @else
        <section class="bg-twende-dark text-white">
            <div class="mx-auto flex min-h-[20rem] max-w-[100rem] flex-col justify-center px-4 py-10 sm:min-h-[28rem] lg:min-h-[32rem] lg:px-14">
                <p class="text-sm font-semibold uppercase tracking-wide text-white/80">{{ __('ui.store.welcome') }}</p>
                <h1 class="mt-2 text-3xl font-bold leading-tight sm:text-5xl">{{ __('ui.home.title') }}</h1>
                <p class="mt-3 text-lg font-semibold sm:text-2xl">{{ __('ui.store.slogan') }}</p>
                <p class="mt-2 max-w-xl text-sm text-white/90">{{ __('ui.store.welcome_line') }}</p>
                <p class="mt-2 max-w-xl text-sm text-white/70">{{ __('ui.home.subtitle') }}</p>
                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="{{ route('products.index') }}" class="inline-flex h-11 items-center rounded-full bg-twende-red px-5 text-sm font-semibold text-white">{{ __('commerce.buy_now') }}</a>
                    <a href="{{ route('sell') }}" class="inline-flex h-11 items-center rounded-full bg-white px-5 text-sm font-semibold text-twende-dark">{{ __('commerce.become_vendor') }}</a>
                </div>
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-[100rem] px-3 py-4 sm:px-4" aria-labelledby="categories-title">
        <div class="flex items-end justify-between gap-3">
            <h2 id="categories-title" class="text-lg font-bold">{{ __('ui.store.popular_categories') }}</h2>
            <a href="{{ route('categories.index') }}" class="text-sm font-semibold text-twende-green">{{ __('ui.home.categories_title') }}</a>
        </div>
        @if ($categories->isEmpty())
            <div class="mt-3">
                <x-empty-state :title="__('ui.home.categories_empty')" />
            </div>
        @else
            <div class="mt-3 flex gap-2 overflow-x-auto pb-1 md:grid md:grid-cols-6 md:overflow-visible lg:grid-cols-8 xl:grid-cols-12">
                @foreach ($categories as $category)
                    <x-category-card :name="$category->name" :href="route('categories.show', $category)" :image="$category->imageUrl()" :count="trans_choice('ui.catalog.child_count', $category->children_count, ['count' => $category->children_count])" />
                @endforeach
            </div>
        @endif
    </section>

    <x-product-rail :title="__('ui.home.bestsellers')" :products="$bestsellers" :empty="__('ui.home.bestsellers_empty')" :href="route('products.index', ['sort' => 'bestsellers'])" />
    <x-product-rail :title="__('ui.store.offers_now')" :kicker="__('ui.home.promotions')" :products="$promotions" :empty="__('ui.home.promotions_empty')" :href="route('promotions')" />
    <x-product-rail :title="__('ui.home.popular')" :products="$popular" :empty="__('ui.home.popular_empty')" :href="route('products.index')" />
    <x-product-rail :title="__('ui.home.newest')" :products="$newest" :empty="__('ui.home.newest_empty')" :href="route('products.index', ['sort' => 'newest'])" />

    @if ($flash->isNotEmpty())
        <x-product-rail :title="__('experience.flash')" :products="$flash" :href="route('promotions')" />
    @endif

    <section class="mx-auto max-w-[100rem] px-3 py-3 sm:px-4" aria-labelledby="section-shops">
        <div class="flex items-end justify-between gap-3">
            <div>
                <h2 id="section-shops" class="text-lg font-bold">{{ __('ui.store.suppliers') }}</h2>
                <p class="text-xs font-semibold text-twende-muted">{{ __('ui.home.shops') }}</p>
            </div>
            <a href="{{ route('shops.index') }}" class="text-sm font-semibold text-twende-green">{{ __('ui.catalog.see_all') }}</a>
        </div>
        <div class="mt-2">
            @if ($shops->isEmpty())
                <x-empty-state :title="__('ui.home.shops_empty')" />
            @else
                <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($shops as $shop)
                        <x-supplier-card :shop="$shop" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="mx-auto max-w-[100rem] px-3 py-3 sm:px-4">
        <h2 class="text-lg font-bold">{{ __('ui.store.popular_vendors') }}</h2>
        <div class="mt-2 grid grid-cols-2 gap-2 md:grid-cols-4">
            @forelse ($vendors as $vendor)
                <article class="rounded-lg border border-twende-line bg-white p-3 dark:border-white/10 dark:bg-twende-night-card">
                    <h3 class="text-sm font-semibold">{{ $vendor->business_name ?: $vendor->user?->name }}</h3>
                    @if ($vendor->isCertified())
                        <p class="mt-1 text-xs font-semibold text-twende-green">{{ __('commerce.certified') }}</p>
                    @endif
                </article>
            @empty
                <x-empty-state :title="__('ui.home.shops_empty')" />
            @endforelse
        </div>
    </section>

    <x-product-rail :title="__('ui.store.recommended')" :kicker="__('experience.recommended')" :products="$recommended" :empty="__('ui.home.popular_empty')" :href="route('products.index')" />

    <section class="mx-auto max-w-[100rem] px-3 py-4 sm:px-4">
        <div class="rounded-lg border border-twende-red/20 bg-white p-4 dark:border-white/10 dark:bg-twende-night-card">
            <h2 class="text-lg font-bold">{{ __('experience.referral_banner') }}</h2>
            <p class="mt-1 max-w-xl text-sm text-twende-muted">{{ __('experience.referral_banner_body') }}</p>
            <a href="{{ auth()->check() ? route('referral') : route('register') }}" class="mt-3 inline-flex h-10 items-center rounded-full bg-twende-red px-4 text-sm font-semibold text-white">{{ __('commerce.referral') }}</a>
        </div>
    </section>

    <section class="mx-auto max-w-[100rem] px-3 py-3 sm:px-4">
        <h2 class="text-lg font-bold">{{ __('commerce.near_me') }}</h2>
        <div class="mt-2 grid gap-2 sm:grid-cols-3">
            <a href="{{ route('nearby') }}" class="rounded-lg border border-twende-line bg-white p-3 text-sm font-semibold hover:border-twende-green dark:border-white/10 dark:bg-twende-night-card">{{ __('commerce.near_products') }}</a>
            <a href="{{ route('shops.index') }}" class="rounded-lg border border-twende-line bg-white p-3 text-sm font-semibold hover:border-twende-green dark:border-white/10 dark:bg-twende-night-card">{{ __('commerce.near_shops') }}</a>
            <a href="{{ route('promotions') }}" class="rounded-lg border border-twende-line bg-white p-3 text-sm font-semibold hover:border-twende-red dark:border-white/10 dark:bg-twende-night-card">{{ __('commerce.near_promos') }}</a>
        </div>
    </section>

    @if ($ads->isNotEmpty())
        <section class="mx-auto max-w-[100rem] px-3 py-3 sm:px-4">
            <h2 class="text-lg font-bold">{{ __('commerce.ads_title') }}</h2>
            <div class="mt-2 grid gap-2 md:grid-cols-2">
                @foreach ($ads as $ad)
                    <article class="rounded-lg bg-white p-4 dark:bg-twende-night-card">
                        <h3 class="font-bold">{{ $ad->title }}</h3>
                        <p class="mt-1 text-sm">{{ $ad->body }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-[100rem] px-3 py-4 sm:px-4">
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-twende-green px-4 py-4 text-white">
            <h2 class="text-lg font-bold">{{ __('commerce.become_vendor') }}</h2>
            <a href="{{ route('sell') }}" class="inline-flex h-10 items-center rounded-full bg-white px-4 text-sm font-semibold text-twende-green">{{ __('ui.footer.sell') }}</a>
        </div>
    </section>

    <section class="mx-auto max-w-[100rem] px-3 py-4 sm:px-4">
        <h2 class="text-lg font-bold">{{ __('ui.home.steps_title') }}</h2>
        <ol class="mt-2 grid gap-2 md:grid-cols-3">
            @foreach ([1, 2, 3] as $step)
                <li class="rounded-lg border border-twende-line bg-white p-3 dark:border-white/10 dark:bg-twende-night-card">
                    <span class="text-xs font-bold text-twende-red">{{ $step }}</span>
                    <h3 class="font-semibold">{{ __('ui.home.step_'.$step.'_title') }}</h3>
                    <p class="mt-1 text-sm text-twende-muted">{{ __('ui.home.step_'.$step.'_body') }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    <section class="mx-auto max-w-[100rem] px-3 py-6 sm:px-4" aria-labelledby="roles-title">
        <h2 id="roles-title" class="text-lg font-bold">{{ __('ui.home.roles_title') }}</h2>
        <div class="mt-2 grid gap-2 md:grid-cols-3">
            <x-shop-card :name="__('ui.home.client_title')" :description="__('ui.home.client_body')" :href="route('register')" />
            <x-shop-card :name="__('ui.home.vendor_title')" :description="__('ui.home.vendor_body')" :href="route('register')" />
            <x-shop-card :name="__('ui.home.delivery_title')" :description="__('ui.home.delivery_body')" :href="route('register')" />
        </div>
    </section>

    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => config('twende.name'),
            'url' => url('/'),
            'logo' => asset(config('twende.logo')),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
</x-layouts.storefront>
