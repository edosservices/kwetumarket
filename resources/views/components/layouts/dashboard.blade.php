@props([
    'title' => null,
    'description' => null,
])

<x-layouts.base :title="$title" :description="$description">
    <div class="flex min-h-screen flex-col bg-twende-light dark:bg-twende-night">
        <x-site-header />
        <div class="flex min-h-0 min-w-0 flex-1" x-data="{ sidebar: false }">
            <div x-show="sidebar" x-cloak x-transition.opacity class="fixed inset-0 z-40 bg-twende-dark/40 lg:hidden" x-on:click="sidebar = false"></div>
            <aside
                class="z-50 flex w-64 max-w-[85vw] shrink-0 flex-col bg-twende-dark p-3 text-white transition-transform max-lg:fixed max-lg:inset-y-0 max-lg:left-0 lg:static"
                x-bind:class="sidebar ? 'max-lg:translate-x-0' : 'max-lg:-translate-x-full'"
            >
                <div class="flex items-center justify-between px-1">
                    <p class="text-xs font-semibold uppercase tracking-wide text-white/60">{{ config('twende.name') }}</p>
                    <button type="button" class="rounded-md p-2 lg:hidden" x-on:click="sidebar = false" aria-label="{{ __('ui.nav.close') }}">
                        <x-icon name="close" class="h-5 w-5" />
                    </button>
                </div>
                <nav class="mt-4 flex flex-1 flex-col gap-4 overflow-y-auto" aria-label="{{ __('ui.nav.dashboard') }}">
                    <div class="grid gap-0.5">
                        <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wide text-white/45">{{ __('ui.nav.account') }}</p>
                        <x-sidebar-link inverted :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('ui.nav.dashboard') }}</x-sidebar-link>
                        <x-sidebar-link inverted :href="route('profile.edit')" :active="request()->routeIs('profile.edit')">{{ __('ui.nav.profile') }}</x-sidebar-link>
                        <x-sidebar-link inverted :href="route('profile.edit').'#securite'">{{ __('ui.dashboard.security_title') }}</x-sidebar-link>
                        <x-sidebar-link inverted :href="route('profile.edit').'#parametres'">{{ __('ui.dashboard.settings') }}</x-sidebar-link>
                        @can('orders.view-own')
                            <x-sidebar-link inverted :href="route('orders.index')" :active="request()->routeIs('orders.*')">{{ __('commerce.orders') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('addresses.index')" :active="request()->routeIs('addresses.*')">{{ __('operations.addresses') }}</x-sidebar-link>
                        @endcan
                        @can('wishlist.manage')
                            <x-sidebar-link inverted :href="route('favorites.index')" :active="request()->routeIs('favorites.*')">{{ __('commerce.favorites') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('follows.index')" :active="request()->routeIs('follows.*')">{{ __('commerce.follows') }}</x-sidebar-link>
                        @endcan
                        @can('messages.create')
                            <x-sidebar-link inverted :href="route('messages.index')" :active="request()->routeIs('messages.*')">{{ __('commerce.messages') }}</x-sidebar-link>
                        @endcan
                        <x-sidebar-link inverted :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">{{ __('commerce.notifications') }}</x-sidebar-link>
                    </div>
                    @role('vendor|admin')
                        <div class="grid gap-0.5">
                            <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wide text-white/45">{{ __('ui.nav.vendor') }}</p>
                            <x-sidebar-link inverted :href="route('vendor.dashboard')" :active="request()->routeIs('vendor.dashboard')">{{ __('ui.nav.dashboard') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('vendor.products.index')" :active="request()->routeIs('vendor.products.index', 'vendor.products.edit')">{{ __('ui.catalog.my_products') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('vendor.products.create')" :active="request()->routeIs('vendor.products.create')">{{ __('ui.catalog.product_create') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('vendor.orders.index')" :active="request()->routeIs('vendor.orders.*')">{{ __('commerce.orders') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('vendor.analytics')" :active="request()->routeIs('vendor.analytics')">{{ __('ui.dashboard.sales') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('vendor.customers')" :active="request()->routeIs('vendor.customers')">{{ __('ui.dashboard.clients') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('vendor.inventory.index')" :active="request()->routeIs('vendor.inventory.*')">{{ __('ui.catalog.stock') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('vendor.promotions')" :active="request()->routeIs('vendor.promotions')">{{ __('ui.home.promotions') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('vendor.shop.edit')" :active="request()->routeIs('vendor.shop.*')">{{ __('ui.catalog.my_shop') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('messages.index')" :active="false">{{ __('commerce.messages') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('profile.edit').'#parametres'">{{ __('ui.dashboard.settings') }}</x-sidebar-link>
                        </div>
                    @endrole
                    @role('delivery_agent|admin')
                        <div class="grid gap-0.5">
                            <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wide text-white/45">{{ __('ui.nav.delivery') }}</p>
                            <x-sidebar-link inverted :href="route('delivery.dashboard')" :active="request()->routeIs('delivery.dashboard')">{{ __('ui.nav.dashboard') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('delivery.jobs')" :active="request()->routeIs('delivery.jobs')">{{ __('commerce.missions') }}</x-sidebar-link>
                        </div>
                    @endrole
                    @role('admin')
                        <div class="grid gap-0.5">
                            <p class="px-3 pb-1 text-[11px] font-semibold uppercase tracking-wide text-white/45">{{ __('ui.nav.admin') }}</p>
                            <x-sidebar-link inverted :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">{{ __('ui.nav.dashboard') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*') && request('role') === null">{{ __('commerce.users') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('admin.users.index', ['role' => 'client'])" :active="request('role') === 'client'">{{ __('ui.dashboard.clients') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('admin.vendors.index')" :active="request()->routeIs('admin.vendors.*')">{{ __('ui.catalog.vendors') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*')">{{ __('ui.catalog.products_title') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('admin.categories.index')" :active="request()->routeIs('admin.categories.*')">{{ __('ui.catalog.categories_title') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('admin.orders.index')" :active="request()->routeIs('admin.orders.*')">{{ __('commerce.orders') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('admin.payments.index')" :active="request()->routeIs('admin.payments.*')">{{ __('ui.dashboard.payments') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('admin.deliveries.index')" :active="request()->routeIs('admin.deliveries.*')">{{ __('ui.dashboard.deliveries') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('admin.ads.index')" :active="request()->routeIs('admin.ads.*')">{{ __('ui.home.promotions') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('admin.reviews.index')" :active="request()->routeIs('admin.reviews.*')">{{ __('commerce.reviews') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('admin.disputes.index')" :active="request()->routeIs('admin.disputes.*')">{{ __('ui.dashboard.reports') }}</x-sidebar-link>
                            <x-sidebar-link inverted :href="route('admin.settings')" :active="request()->routeIs('admin.settings')">{{ __('ui.dashboard.settings') }}</x-sidebar-link>
                        </div>
                    @endrole
                </nav>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="h-9 w-full rounded-md border border-white/15 text-sm font-semibold text-white">{{ __('ui.nav.logout') }}</button>
                </form>
            </aside>
            <div class="flex min-w-0 flex-1 flex-col">
                <div class="flex items-center justify-between gap-3 border-b border-twende-line bg-white px-3 py-2 dark:border-white/10 dark:bg-twende-night lg:hidden">
                    <button type="button" class="inline-flex h-9 items-center gap-2 rounded-md px-2 text-sm font-semibold" x-on:click="sidebar = true" aria-label="{{ __('ui.nav.menu') }}">
                        <x-icon name="menu" class="h-5 w-5" />
                        {{ __('ui.nav.menu') }}
                    </button>
                    <x-theme-toggle />
                </div>
                <main id="contenu" class="account-canvas min-w-0 flex-1 overflow-x-clip px-3 py-4 sm:px-5">
                    <x-flash />
                    {{ $slot }}
                </main>
            </div>
        </div>
    </div>
</x-layouts.base>
