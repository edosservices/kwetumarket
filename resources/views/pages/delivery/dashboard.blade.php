<x-layouts.delivery :title="__('ui.areas.delivery')">
    <h1 class="text-2xl font-bold sm:text-3xl">{{ __('ui.areas.delivery') }}</h1>
    <p class="mt-2 max-w-2xl text-sm text-twende-muted">{{ __('ui.dashboard.delivery_intro') }}</p>
    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-stat-card :label="__('ui.stats.assigned')" :value="$stats['assigned']" data-stat="assigned" />
        <x-stat-card :label="__('ui.stats.active')" :value="$stats['active']" />
        <x-stat-card :label="__('ui.stats.delivered')" :value="$stats['delivered']" />
        <x-stat-card :label="__('ui.stats.failed')" :value="$stats['failed']" />
        <x-stat-card :label="__('ui.stats.earnings')" :value="$stats['earnings_label']" data-stat="earnings" />
        @can('delivery.manage')
            <x-stat-card :label="__('ui.stats.fleet')" :value="$stats['fleet']" />
        @endcan
    </div>
</x-layouts.delivery>
