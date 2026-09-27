<x-layouts.vendor :title="__('ui.areas.vendor')">
    <h1 class="text-2xl font-bold sm:text-3xl">{{ __('ui.areas.vendor') }}</h1>
    <p class="mt-2 max-w-2xl text-sm text-twende-muted">{{ __('ui.dashboard.vendor_intro') }}</p>
    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @can('products.view')
            <x-stat-card :label="__('ui.stats.products')" :value="$stats['products']" />
        @endcan
        @can('orders.view')
            <x-stat-card :label="__('ui.stats.orders')" :value="$stats['orders']" />
            <x-stat-card :label="__('ui.stats.customers')" :value="$stats['customers']" />
        @endcan
        @can('finance.view')
            <x-stat-card :label="__('ui.stats.revenue')" :value="$stats['revenue_label']" />
        @endcan
        @can('inventory.view')
            <x-stat-card :label="__('ui.stats.stock')" :value="$stats['stock']" />
            <x-stat-card :label="__('ui.stats.low_stock')" :value="$stats['low_stock']" />
        @endcan
    </div>
</x-layouts.vendor>
