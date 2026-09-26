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
                @role('vendor|admin')
                    <x-sidebar-link :href="route('vendor.dashboard')" :active="request()->routeIs('vendor.dashboard')">{{ __('ui.nav.vendor') }}</x-sidebar-link>
                @endrole
                @role('delivery_agent|admin')
                    <x-sidebar-link :href="route('delivery.dashboard')" :active="request()->routeIs('delivery.dashboard')">{{ __('ui.nav.delivery') }}</x-sidebar-link>
                @endrole
                @role('admin')
                    <x-sidebar-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">{{ __('ui.nav.admin') }}</x-sidebar-link>
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
            <main id="contenu" class="flex-1 px-4 py-6 sm:px-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.base>
