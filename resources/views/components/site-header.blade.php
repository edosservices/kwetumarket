<header class="sticky top-0 z-40 border-b border-twende-line bg-white/95 backdrop-blur dark:border-white/10 dark:bg-twende-night/95" x-data="{ open: false }">
    <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-3">
        <x-brand-logo size="sm" :href="route('home')" />

        <div class="hidden min-w-0 flex-1 md:block">
            <livewire:marketplace-search variant="header" />
        </div>

        <nav class="ml-auto hidden items-center gap-1 lg:flex" aria-label="{{ __('ui.nav.home') }}">
            <a href="{{ route('categories.index') }}" class="rounded-full px-3 py-2 text-sm font-medium hover:text-twende-red">{{ __('ui.nav.categories') }}</a>
            <a href="{{ route('shops.index') }}" class="rounded-full px-3 py-2 text-sm font-medium hover:text-twende-red">{{ __('ui.nav.shops') }}</a>
        </nav>

        <div class="ml-auto flex items-center gap-1 md:ml-0">
            <a href="{{ route('cart.show') }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full hover:bg-twende-light dark:hover:bg-white/10" aria-label="{{ __('ui.nav.cart') }}">
                <x-icon name="cart" />
            </a>
            <div class="hidden sm:flex">
                <x-theme-toggle />
            </div>
            @auth
                <a href="{{ route('dashboard') }}" class="hidden rounded-full px-3 py-2 text-sm font-semibold text-twende-dark hover:text-twende-red sm:inline dark:text-white">{{ __('ui.nav.account') }}</a>
            @else
                <div class="hidden items-center gap-1 sm:flex">
                    <x-button :href="route('login')" variant="ghost" size="sm">{{ __('ui.nav.login') }}</x-button>
                    <x-button :href="route('register')" size="sm">{{ __('ui.nav.register') }}</x-button>
                </div>
            @endauth
            <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full lg:hidden" x-on:click="open = ! open" x-bind:aria-expanded="open.toString()" aria-controls="menu-mobile" aria-label="{{ __('ui.nav.menu') }}">
                <x-icon name="menu" />
            </button>
        </div>
    </div>

    <div id="menu-mobile" class="border-t border-twende-line px-4 py-4 lg:hidden dark:border-white/10" x-show="open" x-cloak>
        <livewire:marketplace-search variant="header" />
        <nav class="mt-4 flex flex-col gap-1">
            <a href="{{ route('home') }}" class="rounded-xl px-3 py-2 font-medium">{{ __('ui.nav.home') }}</a>
            <a href="{{ route('categories.index') }}" class="rounded-xl px-3 py-2 font-medium">{{ __('ui.nav.categories') }}</a>
            <a href="{{ route('shops.index') }}" class="rounded-xl px-3 py-2 font-medium">{{ __('ui.nav.shops') }}</a>
            <a href="{{ route('cart.show') }}" class="rounded-xl px-3 py-2 font-medium">{{ __('ui.nav.cart') }}</a>
            @auth
                <a href="{{ route('dashboard') }}" class="rounded-xl px-3 py-2 font-medium">{{ __('ui.nav.dashboard') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-xl px-3 py-2 text-left font-medium text-twende-red">{{ __('ui.nav.logout') }}</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="rounded-xl px-3 py-2 font-medium">{{ __('ui.nav.login') }}</a>
                <a href="{{ route('register') }}" class="rounded-xl px-3 py-2 font-semibold text-twende-red">{{ __('ui.nav.register') }}</a>
            @endauth
        </nav>
        <div class="mt-3">
            <x-theme-toggle />
        </div>
    </div>
</header>
