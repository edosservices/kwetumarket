@props([
    'title' => null,
    'description' => null,
    'area' => 'customer',
])

@php
    $accountArea = \App\Enums\AccountArea::from($area);
    $navigation = \App\Support\Rbac\Navigation::for(auth()->user(), $accountArea);
    $home = route($accountArea->homeRoute());
@endphp

<x-layouts.base :title="$title" :description="$description">
    <div class="min-h-screen" x-data="{ sidebar: false }">
        <div class="lg:grid lg:grid-cols-[17rem_minmax(0,1fr)]">
            <aside class="hidden border-r border-twende-line bg-white lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col lg:p-4 dark:border-white/10 dark:bg-twende-night-card">
                <x-brand-logo size="sm" :href="$home" />
                <nav class="mt-6 flex min-h-0 flex-1 flex-col gap-1 overflow-y-auto" aria-label="{{ __('ui.nav.dashboard') }}">
                    @foreach ($navigation as $item)
                        <x-sidebar-link :href="$item['href']" :active="$item['active']">{{ $item['label'] }}</x-sidebar-link>
                    @endforeach
                </nav>
                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <x-button type="submit" variant="outline" class="w-full">{{ __('ui.nav.logout') }}</x-button>
                </form>
            </aside>

            <div class="min-w-0">
                <header class="sticky top-0 z-40 flex h-14 items-center gap-3 border-b border-twende-line bg-white px-4 dark:border-white/10 dark:bg-twende-night">
                    <button type="button" class="rounded-full p-2 lg:hidden" x-on:click="sidebar = true" aria-label="{{ __('ui.nav.menu') }}">
                        <x-icon name="menu" class="h-5 w-5" />
                    </button>
                    <x-brand-logo size="sm" :href="$home" class="lg:hidden" />
                    <p class="min-w-0 flex-1 truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                    <div class="ml-auto flex shrink-0 items-center gap-1">
                        <a href="{{ route('notifications.index') }}" class="inline-flex h-10 items-center gap-1 rounded-full px-2 text-sm font-semibold text-twende-dark hover:text-twende-red dark:text-white" aria-label="{{ __('ui.modules.notifications') }}">
                            <span class="hidden sm:inline">{{ __('ui.modules.notifications') }}</span>
                            <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-twende-red px-1.5 text-xs font-bold text-white">{{ auth()->user()->unreadNotifications()->count() }}</span>
                        </a>
                        <x-theme-toggle />
                    </div>
                </header>
                <main id="contenu" class="px-4 py-6 sm:px-8">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <div
            x-show="sidebar"
            x-cloak
            x-transition.opacity
            class="fixed inset-x-0 bottom-0 top-14 z-30 bg-twende-dark/40 lg:hidden"
            x-on:click="sidebar = false"
        ></div>
        <aside
            x-show="sidebar"
            x-cloak
            x-transition
            class="fixed bottom-0 left-0 top-14 z-30 flex w-72 max-w-[85vw] flex-col overflow-y-auto border-r border-twende-line bg-white p-4 lg:hidden dark:border-white/10 dark:bg-twende-night-card"
        >
            <nav class="flex flex-1 flex-col gap-1" aria-label="{{ __('ui.nav.dashboard') }}">
                @foreach ($navigation as $item)
                    <x-sidebar-link :href="$item['href']" :active="$item['active']">{{ $item['label'] }}</x-sidebar-link>
                @endforeach
            </nav>
            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <x-button type="submit" variant="outline" class="w-full">{{ __('ui.nav.logout') }}</x-button>
            </form>
        </aside>
    </div>
</x-layouts.base>
