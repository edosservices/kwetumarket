<x-layouts.storefront :title="__('ui.home.title')" :description="__('ui.home.subtitle')">
    <section class="border-b border-twende-line bg-twende-light dark:border-white/10 dark:bg-twende-night-card" aria-label="{{ __('ui.nav.search') }}">
        <div class="mx-auto max-w-3xl px-4 py-6 sm:py-8">
            <livewire:marketplace-search variant="hero" />
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10" aria-labelledby="categories-title">
        <h2 id="categories-title" class="text-xl font-bold sm:text-2xl">{{ __('ui.home.categories_title') }}</h2>
        <div class="mt-4">
            <x-empty-state :title="__('ui.home.categories_empty')" />
        </div>
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
        'popular' => 'popular_empty',
        'promotions' => 'promotions_empty',
        'bestsellers' => 'bestsellers_empty',
        'shops' => 'shops_empty',
        'newest' => 'newest_empty',
    ] as $title => $empty)
        <section class="mx-auto max-w-7xl px-4 py-6" aria-labelledby="section-{{ $title }}">
            <h2 id="section-{{ $title }}" class="text-xl font-bold sm:text-2xl">{{ __('ui.home.'.$title) }}</h2>
            <div class="mt-4">
                <x-empty-state :title="__('ui.home.'.$empty)" />
            </div>
        </section>
    @endforeach

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
