<x-layouts.storefront :title="__('ui.home.title')" :description="__('ui.home.subtitle')">
    <section class="border-b border-twende-line bg-twende-light dark:border-white/10 dark:bg-twende-night-card" aria-label="{{ __('ui.nav.search') }}">
        <div class="mx-auto max-w-3xl px-4 py-6 sm:py-8">
            <livewire:marketplace-search variant="hero" />
        </div>
    </section>

    @if ($slides->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-6">
            <x-hero-carousel :slides="$slides" />
        </section>
    @endif

    <section class="mx-auto max-w-7xl px-4 py-10" aria-labelledby="categories-title">
        <div class="flex items-end justify-between gap-3">
            <h2 id="categories-title" class="text-xl font-bold sm:text-2xl">{{ __('ui.home.categories_title') }}</h2>
            <a href="{{ route('categories.index') }}" class="text-sm font-semibold text-twende-green">{{ __('ui.catalog.see_all') }}</a>
        </div>
        @if ($categories->isEmpty())
            <div class="mt-4">
                <x-empty-state :title="__('ui.home.categories_empty')" />
            </div>
        @else
            <div class="mt-4 flex gap-3 overflow-x-auto pb-2">
                @foreach ($categories as $category)
                    <x-category-card :name="$category->name" :href="route('categories.show', $category)" :count="trans_choice('ui.catalog.child_count', $category->children_count, ['count' => $category->children_count])" />
                @endforeach
            </div>
        @endif
    </section>

    <section class="bg-gradient-to-br from-twende-red/5 via-white to-twende-green/10 dark:from-twende-red/10 dark:via-twende-night dark:to-twende-green/10">
        <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-12 lg:grid-cols-2 lg:py-16">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-twende-green">{{ config('twende.name') }}</p>
                <h1 class="mt-3 text-4xl font-bold leading-tight text-twende-dark sm:text-5xl dark:text-white">{{ __('ui.home.title') }}</h1>
                <p class="mt-4 max-w-xl text-base leading-relaxed text-twende-muted sm:text-lg">{{ __('ui.home.subtitle') }}</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <x-button :href="route('register')" size="lg">{{ __('ui.home.cta_register') }}</x-button>
                    <x-button :href="route('search')" variant="outline" size="lg">{{ __('ui.home.cta_search') }}</x-button>
                </div>
                <ul class="mt-8 grid gap-3 sm:grid-cols-2">
                    @foreach (['trust_local', 'trust_currency', 'trust_delivery', 'trust_secure'] as $trust)
                        <li class="rounded-2xl bg-white px-3 py-3 text-sm font-semibold text-twende-dark shadow-sm dark:bg-twende-night-card dark:text-white">{{ __('ui.home.'.$trust) }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="flex items-center justify-center rounded-3xl bg-white p-6 shadow-sm ring-1 ring-twende-line dark:ring-white/10">
                <x-brand-logo size="xl" />
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12">
        <h2 class="text-xl font-bold sm:text-2xl">{{ __('ui.home.steps_title') }}</h2>
        <ol class="mt-6 grid gap-4 md:grid-cols-3">
            @foreach ([1, 2, 3] as $step)
                <li class="rounded-2xl border border-twende-line bg-white p-5 dark:border-white/10 dark:bg-twende-night-card">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-twende-red text-sm font-bold text-white">{{ $step }}</span>
                    <h3 class="mt-4 font-semibold">{{ __('ui.home.step_'.$step.'_title') }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-twende-muted">{{ __('ui.home.step_'.$step.'_body') }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    @foreach ([
        'popular' => ['empty' => 'popular_empty', 'items' => $popular, 'type' => 'product'],
        'promotions' => ['empty' => 'promotions_empty', 'items' => $promotions, 'type' => 'product'],
        'bestsellers' => ['empty' => 'bestsellers_empty', 'items' => $bestsellers, 'type' => 'product'],
        'shops' => ['empty' => 'shops_empty', 'items' => $shops, 'type' => 'shop'],
        'newest' => ['empty' => 'newest_empty', 'items' => $newest, 'type' => 'product'],
    ] as $title => $section)
        <section class="mx-auto max-w-7xl px-4 py-6" aria-labelledby="section-{{ $title }}">
            <h2 id="section-{{ $title }}" class="text-xl font-bold sm:text-2xl">{{ __('ui.home.'.$title) }}</h2>
            <div class="mt-4">
                @if ($section['items']->isEmpty())
                    <x-empty-state :title="__('ui.home.'.$section['empty'])" />
                @elseif ($section['type'] === 'shop')
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($section['items'] as $shop)
                            <x-shop-card :name="$shop->name" :description="$shop->description" :location="$shop->location" :href="route('shops.show', $shop)" />
                        @endforeach
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-4 md:gap-4">
                        @foreach ($section['items'] as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endforeach

    @if ($flash->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-6">
            <h2 class="text-xl font-bold sm:text-2xl text-twende-red">{{ __('experience.flash') }}</h2>
            <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
                @foreach ($flash as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif
    @if ($recommended->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-6">
            <h2 class="text-xl font-bold sm:text-2xl">{{ __('experience.recommended') }}</h2>
            <div class="mt-4 grid grid-cols-2 gap-3 md:grid-cols-4">
                @foreach ($recommended as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif
    <section class="mx-auto max-w-7xl px-4 py-8">
        <div class="rounded-3xl border border-twende-red/20 bg-twende-red/5 p-6 dark:bg-twende-red/10">
            <h2 class="text-2xl font-bold">{{ __('experience.referral_banner') }}</h2>
            <p class="mt-2 max-w-xl text-sm text-twende-muted">{{ __('experience.referral_banner_body') }}</p>
            <a href="{{ auth()->check() ? route('referral') : route('register') }}" class="mt-4 inline-flex h-11 items-center rounded-full bg-twende-red px-5 text-sm font-semibold text-white">{{ __('commerce.referral') }}</a>
        </div>
    </section>
    <section class="mx-auto max-w-7xl px-4 py-8">
        <h2 class="text-xl font-bold sm:text-2xl">{{ __('commerce.near_me') }}</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-3">
            <a href="{{ route('nearby') }}" class="rounded-2xl border border-twende-line p-4 font-semibold hover:border-twende-green dark:border-white/10">{{ __('commerce.near_products') }}</a>
            <a href="{{ route('shops.index') }}" class="rounded-2xl border border-twende-line p-4 font-semibold hover:border-twende-green dark:border-white/10">{{ __('commerce.near_shops') }}</a>
            <a href="{{ route('promotions') }}" class="rounded-2xl border border-twende-line p-4 font-semibold hover:border-twende-red dark:border-white/10">{{ __('commerce.near_promos') }}</a>
        </div>
    </section>
    <section class="mx-auto max-w-7xl px-4 py-6">
        <h2 class="text-xl font-bold sm:text-2xl">{{ __('commerce.recommended_vendors') }}</h2>
        <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($vendors as $vendor)
                <article class="rounded-2xl border border-twende-line p-4 dark:border-white/10">
                    <h3 class="font-semibold">{{ $vendor->business_name ?: $vendor->user?->name }}</h3>
                    @if ($vendor->isCertified())
                        <p class="mt-2 text-xs font-semibold text-twende-green">{{ __('commerce.certified') }}</p>
                    @endif
                </article>
            @empty
                <x-empty-state :title="__('ui.home.shops_empty')" />
            @endforelse
        </div>
    </section>
    @if ($ads->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-6">
            <h2 class="text-xl font-bold">{{ __('commerce.ads_title') }}</h2>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                @foreach ($ads as $ad)
                    <article class="rounded-3xl bg-twende-red/10 p-5 dark:bg-twende-red/20">
                        <h3 class="text-lg font-bold">{{ $ad->title }}</h3>
                        <p class="mt-2 text-sm">{{ $ad->body }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
    <section class="mx-auto max-w-7xl px-4 py-8">
        <div class="rounded-3xl bg-twende-green px-6 py-8 text-white">
            <h2 class="text-2xl font-bold">{{ __('commerce.become_vendor') }}</h2>
            <a href="{{ route('sell') }}" class="mt-4 inline-flex h-11 items-center rounded-full bg-white px-5 text-sm font-semibold text-twende-green">{{ __('ui.footer.sell') }}</a>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12" aria-labelledby="roles-title">
        <h2 id="roles-title" class="text-xl font-bold sm:text-2xl">{{ __('ui.home.roles_title') }}</h2>
        <div class="mt-6 grid gap-4 md:grid-cols-3">
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
