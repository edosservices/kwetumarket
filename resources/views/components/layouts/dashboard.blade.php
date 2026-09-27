@props([
    'title' => null,
    'description' => null,
])

<x-layouts.base :title="$title" :description="$description">
    <div class="flex min-h-screen flex-col lg:flex-row" x-data="{ sidebar: false }">
        <div
            x-show="sidebar"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-40 bg-twende-dark/40 lg:hidden"
            x-on:click="sidebar = false"
        ></div>
        <aside
            class="z-50 flex w-72 max-w-[85vw] shrink-0 flex-col border-r border-twende-line bg-white p-4 transition-transform max-lg:fixed max-lg:inset-y-0 max-lg:left-0 lg:static dark:border-white/10 dark:bg-twende-night-card"
            x-bind:class="sidebar ? 'max-lg:translate-x-0' : 'max-lg:-translate-x-full'"
        >
            <div class="flex items-center justify-between">
                <x-brand-logo size="sm" :href="route('home')" />
                <button type="button" class="rounded-full p-2 lg:hidden" x-on:click="sidebar = false" aria-label="{{ __('ui.nav.close') }}">
                    <x-icon name="close" class="h-5 w-5" />
                </button>
            </div>
            <nav class="mt-8 flex flex-1 flex-col gap-1" aria-label="{{ __('ui.nav.dashboard') }}">
                <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('ui.nav.dashboard') }}</x-sidebar-link>
                <x-sidebar-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')">{{ __('ui.nav.profile') }}</x-sidebar-link>
                @can('orders.view-own')
                    <x-sidebar-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">{{ __('commerce.orders') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('addresses.index')" :active="request()->routeIs('addresses.*')">{{ __('operations.addresses') }}</x-sidebar-link>
                @endcan
                @can('wishlist.manage')
                    <x-sidebar-link :href="route('favorites.index')" :active="request()->routeIs('favorites.*')">{{ __('commerce.favorites') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('follows.index')" :active="request()->routeIs('follows.*')">{{ __('commerce.follows') }}</x-sidebar-link>
                @endcan
                @can('messages.create')
                    <x-sidebar-link :href="route('messages.index')" :active="request()->routeIs('messages.*')">{{ __('commerce.messages') }}</x-sidebar-link>
                @endcan
                <x-sidebar-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">{{ __('commerce.notifications') }}</x-sidebar-link>
                @role('vendor|admin')
                    <x-sidebar-link :href="route('vendor.dashboard')" :active="request()->routeIs('vendor.dashboard')">{{ __('ui.nav.vendor') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('vendor.shop.edit')" :active="request()->routeIs('vendor.shop.*')">{{ __('ui.catalog.my_shop') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('vendor.products.index')" :active="request()->routeIs('vendor.products.*')">{{ __('ui.catalog.my_products') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('vendor.products.create')" :active="request()->routeIs('vendor.products.create')">{{ __('ui.catalog.product_create') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('vendor.inventory.index')" :active="request()->routeIs('vendor.inventory.*')">{{ __('ui.catalog.stock') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('vendor.orders.index')" :active="request()->routeIs('vendor.orders.*')">{{ __('commerce.orders') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('vendor.promotions')" :active="request()->routeIs('vendor.promotions')">{{ __('ui.home.promotions') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('vendor.analytics')" :active="request()->routeIs('vendor.analytics')">{{ __('commerce.analytics') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('vendor.wallet')" :active="request()->routeIs('vendor.wallet')">{{ __('commerce.wallet') }}</x-sidebar-link>
                @endrole
                @role('delivery_agent|admin')
                    <x-sidebar-link :href="route('delivery.dashboard')" :active="request()->routeIs('delivery.dashboard')">{{ __('ui.nav.delivery') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('delivery.jobs')" :active="request()->routeIs('delivery.jobs')">{{ __('commerce.missions') }}</x-sidebar-link>
                @endrole
                @role('admin')
                    <x-sidebar-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">{{ __('ui.nav.admin') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">{{ __('commerce.users') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('admin.orders.index')" :active="request()->routeIs('admin.orders.*')">{{ __('commerce.orders') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('admin.deliveries.index')" :active="request()->routeIs('admin.deliveries.*')">{{ __('ui.dashboard.deliveries') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('admin.refunds.index')" :active="request()->routeIs('admin.refunds.*')">{{ __('commerce.refunds') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('admin.disputes.index')" :active="request()->routeIs('admin.disputes.*')">{{ __('ui.dashboard.disputes') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('admin.ads.index')" :active="request()->routeIs('admin.ads.*')">{{ __('ui.home.promotions') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('admin.settings')" :active="request()->routeIs('admin.settings')">{{ __('ui.dashboard.settings') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('admin.categories.index')" :active="request()->routeIs('admin.categories.*')">{{ __('ui.catalog.categories_title') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('admin.brands.index')" :active="request()->routeIs('admin.brands.*')">{{ __('ui.catalog.brands') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('admin.vendors.index')" :active="request()->routeIs('admin.vendors.*')">{{ __('ui.catalog.vendors') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('admin.shops.index')" :active="request()->routeIs('admin.shops.*')">{{ __('ui.catalog.shops_title') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*')">{{ __('ui.catalog.products_title') }}</x-sidebar-link>
                    <x-sidebar-link :href="route('admin.inventory.index')" :active="request()->routeIs('admin.inventory.*')">{{ __('ui.catalog.stock') }}</x-sidebar-link>
                @endrole
            </nav>
            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <x-button type="submit" variant="outline" class="w-full">{{ __('ui.nav.logout') }}</x-button>
            </form>
        </aside>
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex items-center justify-between gap-3 border-b border-twende-line px-4 py-3 dark:border-white/10">
                <button type="button" class="rounded-full p-2 lg:hidden" x-on:click="sidebar = true" aria-label="{{ __('ui.nav.menu') }}">
                    <x-icon name="menu" class="h-5 w-5" />
                </button>
                <p class="truncate text-sm font-medium">{{ auth()->user()->name }}</p>
                <x-theme-toggle />
            </header>
            <main id="contenu" class="min-w-0 flex-1 px-4 py-6 sm:px-8">
                <x-flash />
                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.base>
