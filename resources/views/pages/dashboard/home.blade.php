<x-layouts.dashboard :title="__('ui.nav.dashboard')">
    <h1 class="text-xl font-bold sm:text-2xl">{{ __('ui.dashboard.greeting', ['name' => $user->first_name ?: $user->name]) }}</h1>
    <div class="mt-2">
        <x-badge variant="green">{{ $user->getRoleNames()->map(fn ($role) => __('ui.roles.'.$role))->join(', ') }}</x-badge>
    </div>
    <div class="mt-4 grid grid-cols-2 gap-2 lg:grid-cols-4">
        @can('orders.view-own')
            <x-kpi :label="__('commerce.orders')" :value="$orderCount" :href="route('orders.index')" />
        @endcan
        @can('wishlist.manage')
            <x-kpi :label="__('commerce.favorites')" :value="$favoriteCount" :href="route('favorites.index')" />
        @endcan
        @can('addresses.manage')
            <x-kpi :label="__('operations.addresses')" :value="$addressCount" :href="route('addresses.index')" />
        @endcan
        <x-kpi :label="__('commerce.notifications')" :value="$unreadCount" :href="route('notifications.index')" />
    </div>
    @php
        $links = match ($user->getRoleNames()->first()) {
            'vendor' => [
                __('ui.dashboard.shop') => route('vendor.shop.edit'),
                __('ui.catalog.my_products') => route('vendor.products.index'),
                __('ui.catalog.stock') => route('vendor.inventory.index'),
                __('commerce.orders') => route('vendor.orders.index'),
                __('experience.dropship') => route('vendor.dropship.index'),
                __('operations.import_csv') => route('vendor.import'),
                __('commerce.wallet') => route('vendor.wallet'),
                __('commerce.analytics') => route('vendor.analytics'),
            ],
            'delivery_agent' => [
                __('commerce.missions') => route('delivery.jobs'),
                __('commerce.earnings') => route('delivery.jobs'),
            ],
            'admin' => [
                __('commerce.orders') => route('admin.orders.index'),
                __('commerce.analytics') => route('admin.analytics'),
                __('commerce.settings') => route('admin.settings'),
                __('commerce.users') => route('admin.users.index'),
            ],
            default => [
                __('ui.dashboard.profile_title') => route('profile.edit'),
                __('operations.addresses') => route('addresses.index'),
                __('commerce.orders') => route('orders.index'),
                __('commerce.favorites') => route('favorites.index'),
                __('commerce.follows') => route('follows.index'),
                __('commerce.messages') => route('messages.index'),
                __('commerce.notifications') => route('notifications.index'),
                __('operations.points') => route('points.index'),
                __('commerce.referral') => route('referral'),
                __('operations.coupons') => route('coupons.index'),
                __('operations.my_reviews') => route('reviews.index'),
            ],
        };
    @endphp
    <div class="mt-4 grid grid-cols-2 gap-2 lg:grid-cols-4">
        @foreach ($links as $label => $href)
            <a href="{{ $href }}" class="rounded-lg border border-twende-line bg-white px-3 py-3 text-sm font-semibold hover:border-twende-red dark:border-white/10 dark:bg-twende-night-card">{{ $label }}</a>
        @endforeach
    </div>
</x-layouts.dashboard>
