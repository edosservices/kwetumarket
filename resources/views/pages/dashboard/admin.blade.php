<x-layouts.dashboard :title="__('ui.nav.admin')">
    <h1 class="text-xl font-bold sm:text-2xl">{{ __('ui.nav.admin') }}</h1>
    <p class="mt-1 max-w-2xl text-sm text-twende-muted">{{ __('ui.dashboard.admin_intro') }}</p>
    <div class="mt-4 grid grid-cols-2 gap-2 lg:grid-cols-4">
        <x-kpi :label="__('commerce.users')" :value="$counts['users']" :href="route('admin.users.index')" />
        <x-kpi :label="__('ui.dashboard.clients')" :value="$counts['clients']" :href="route('admin.users.index', ['role' => 'client'])" />
        <x-kpi :label="__('ui.catalog.vendors')" :value="$counts['vendors']" :href="route('admin.vendors.index')" />
        <x-kpi :label="__('ui.catalog.products_title')" :value="$counts['products']" :href="route('admin.products.index')" />
        <x-kpi :label="__('commerce.orders')" :value="$counts['orders']" :href="route('admin.orders.index')" />
        <x-kpi :label="__('ui.dashboard.payments')" :value="$counts['payments']" :href="route('admin.payments.index')" />
        <x-kpi :label="__('ui.dashboard.deliveries')" :value="$counts['deliveries']" :href="route('admin.deliveries.index')" />
        <x-kpi :label="__('commerce.reviews')" :value="$counts['reviews']" :href="route('admin.reviews.index')" />
        <x-kpi :label="__('ui.dashboard.reports')" :value="$counts['disputes']" :href="route('admin.disputes.index')" />
    </div>
    <div class="mt-4 grid grid-cols-2 gap-2 lg:grid-cols-4">
        @foreach ([
            __('ui.catalog.categories_title') => route('admin.categories.index'),
            __('ui.home.promotions') => route('admin.ads.index'),
            __('ui.dashboard.settings') => route('admin.settings'),
            __('ui.catalog.brands') => route('admin.brands.index'),
            __('ui.catalog.shops_title') => route('admin.shops.index'),
            __('ui.catalog.stock') => route('admin.inventory.index'),
        ] as $label => $href)
            <a href="{{ $href }}" class="rounded-lg border border-twende-line bg-white px-3 py-3 text-sm font-semibold hover:border-twende-red dark:border-white/10 dark:bg-twende-night-card">{{ $label }}</a>
        @endforeach
    </div>
</x-layouts.dashboard>
