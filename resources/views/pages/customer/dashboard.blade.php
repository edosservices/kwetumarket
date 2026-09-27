<x-layouts.customer :title="__('ui.areas.customer')">
    <p class="text-sm font-semibold uppercase tracking-wide text-twende-green">{{ __('ui.areas.customer') }}</p>
    <h1 class="mt-2 text-2xl font-bold sm:text-3xl">{{ __('ui.dashboard.greeting', ['name' => $user->name]) }}</h1>
    <p class="mt-2 max-w-2xl text-sm text-twende-muted">{{ __('ui.dashboard.client_intro') }}</p>
    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-stat-card :label="__('ui.stats.orders')" :value="$stats['orders']" data-stat="orders" />
        <x-stat-card :label="__('ui.stats.addresses')" :value="$stats['addresses']" />
        <x-stat-card :label="__('ui.stats.wishlist')" :value="$stats['wishlist']" />
        <x-stat-card :label="__('ui.stats.reviews')" :value="$stats['reviews']" />
        <x-stat-card :label="__('ui.stats.tickets')" :value="$stats['tickets']" />
        <x-stat-card :label="__('ui.stats.notifications')" :value="$stats['notifications']" />
    </div>
</x-layouts.customer>
