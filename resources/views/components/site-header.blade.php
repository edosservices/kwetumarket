@php
    $given = trim((string) (auth()->user()?->first_name ?: auth()->user()?->name));
    $deliverHref = auth()->check() ? route('addresses.index') : route('nearby');
@endphp

<header class="sticky top-0 z-40" x-data="{ open: false, cats: false }">
    <div class="border-b border-twende-line bg-white dark:border-white/10 dark:bg-twende-night">
        <div class="mx-auto flex max-w-[100rem] items-center gap-2 px-2 py-2 sm:gap-3 sm:px-4">
            <x-brand-logo size="sm" :href="route('home')" />

            <a href="{{ $deliverHref }}" class="hidden min-w-0 items-center gap-1 rounded-md px-1 py-1 text-twende-dark hover:text-twende-red lg:flex dark:text-white">
                <x-icon name="pin" class="h-4 w-4 shrink-0 text-twende-green" />
                <span class="leading-tight">
                    <span class="block text-[11px] text-twende-muted">{{ __('ui.store.deliver_label') }}</span>
                    <span class="block max-w-36 truncate text-xs font-bold">{{ $deliveryPlace ?? 'Kinshasa, RDC' }}</span>
                </span>
            </a>

            <div class="min-w-0 flex-1">
                <livewire:marketplace-search variant="header" />
            </div>

            <div class="hidden items-center gap-1 lg:flex">
                @auth
                    <a href="{{ route('notifications.index') }}" class="relative inline-flex h-10 w-10 items-center justify-center rounded-md hover:bg-twende-light dark:hover:bg-white/10" aria-label="{{ __('commerce.notifications') }}">
                        <x-icon name="bell" />
                        @if (($unreadNotifications ?? 0) > 0)
                            <span class="absolute right-1 top-1 inline-flex min-h-4 min-w-4 items-center justify-center rounded-full bg-twende-red px-1 text-[10px] font-bold text-white">{{ $unreadNotifications }}</span>
                        @endif
                    </a>
                @endauth
                <x-theme-toggle />
                @auth
                    <div class="relative" x-data="{ account: false }">
                        <button type="button" class="rounded-md px-2 py-1 text-left leading-tight hover:text-twende-red" x-on:click="account = ! account">
                            <span class="block text-[11px] text-twende-muted">{{ __('ui.dashboard.greeting', ['name' => $given]) }}</span>
                            <span class="block text-sm font-bold">{{ __('ui.nav.account') }} ▾</span>
                        </button>
                        <div x-show="account" x-cloak x-on:click.outside="account = false" class="absolute right-0 top-full z-50 mt-1 w-52 rounded-lg border border-twende-line bg-white py-1 text-sm shadow-lg dark:border-white/10 dark:bg-twende-night-card">
                            <a href="{{ route('dashboard') }}" class="block px-3 py-2 hover:bg-twende-light dark:hover:bg-white/5">{{ __('ui.nav.account') }}</a>
                            <a href="{{ route('orders.index') }}" class="block px-3 py-2 hover:bg-twende-light dark:hover:bg-white/5">{{ __('commerce.orders') }}</a>
                            @can('wishlist.manage')
                                <a href="{{ route('favorites.index') }}" class="block px-3 py-2 hover:bg-twende-light dark:hover:bg-white/5">{{ __('commerce.favorites') }}</a>
                            @endcan
                            @can('messages.create')
                                <a href="{{ route('messages.index') }}" class="block px-3 py-2 hover:bg-twende-light dark:hover:bg-white/5">{{ __('commerce.messages') }}</a>
                            @endcan
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full px-3 py-2 text-left font-semibold text-twende-red">{{ __('ui.nav.logout') }}</button>
                            </form>
                        </div>
                    </div>
                    <a href="{{ route('orders.index') }}" class="rounded-md px-2 py-1 text-sm font-bold leading-tight hover:text-twende-red">{{ __('commerce.orders') }}</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-md px-2 py-1 leading-tight hover:text-twende-red">
                        <span class="block text-[11px] text-twende-muted">{{ __('ui.store.hello') }}</span>
                        <span class="block text-sm font-bold">{{ __('ui.nav.login') }}</span>
                    </a>
                    <a href="{{ route('register') }}" class="rounded-md px-2 py-1 text-sm font-bold text-twende-red">{{ __('ui.nav.register') }}</a>
                @endauth
            </div>

            <a href="{{ route('cart.show') }}" class="relative inline-flex h-10 items-center gap-1 rounded-md px-1.5 hover:bg-twende-light dark:hover:bg-white/10" aria-label="{{ __('ui.nav.cart') }}">
                <x-icon name="cart" class="h-6 w-6" />
                <span class="hidden text-sm font-bold sm:inline">{{ __('ui.nav.cart') }}</span>
                <span class="inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-twende-green-bright px-1 text-[11px] font-bold text-white">{{ $cartCount ?? 0 }}</span>
            </a>
            <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-md lg:hidden" x-on:click="open = ! open" x-bind:aria-expanded="open.toString()" aria-controls="menu-mobile" aria-label="{{ __('ui.nav.menu') }}">
                <x-icon name="menu" />
            </button>
        </div>
    </div>

    <div class="hidden bg-twende-dark text-white md:block">
        <nav class="mx-auto flex max-w-[100rem] items-center gap-1 overflow-x-auto px-2 py-1.5 text-sm sm:px-4" aria-label="{{ __('ui.nav.home') }}">
            <div class="relative shrink-0">
                <button type="button" class="inline-flex h-8 items-center gap-1.5 rounded-md px-2 font-semibold hover:bg-white/10" x-on:click="cats = ! cats" x-bind:aria-expanded="cats.toString()">
                    <x-icon name="menu" class="h-4 w-4" />
                    {{ __('ui.store.all_categories') }}
                </button>
                <div x-show="cats" x-cloak x-on:click.outside="cats = false" class="absolute left-0 top-full z-50 mt-1 w-64 rounded-lg border border-twende-line bg-white py-1 text-twende-dark shadow-lg dark:border-white/10 dark:bg-twende-night-card dark:text-white">
                    @forelse ($headerCategories ?? [] as $category)
                        <a href="{{ route('categories.show', $category) }}" class="block truncate px-3 py-1.5 text-sm hover:bg-twende-light hover:text-twende-red dark:hover:bg-white/5">{{ $category->name }}</a>
                    @empty
                        <a href="{{ route('categories.index') }}" class="block px-3 py-1.5 text-sm">{{ __('ui.store.browse_categories') }}</a>
                    @endforelse
                    <a href="{{ route('categories.index') }}" class="block border-t border-twende-line px-3 py-2 text-sm font-semibold text-twende-green dark:border-white/10">{{ __('ui.catalog.see_all') }}</a>
                </div>
            </div>
            <a href="{{ route('promotions') }}" class="shrink-0 rounded-md px-2.5 py-1 hover:bg-white/10">{{ __('ui.store.offers') }}</a>
            <a href="{{ route('products.index') }}" class="shrink-0 rounded-md px-2.5 py-1 hover:bg-white/10">{{ __('ui.nav.products') }}</a>
            <a href="{{ route('shops.index') }}" class="shrink-0 rounded-md px-2.5 py-1 hover:bg-white/10">{{ __('ui.store.suppliers') }}</a>
            <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="shrink-0 rounded-md px-2.5 py-1 hover:bg-white/10">{{ __('ui.home.newest') }}</a>
            <a href="{{ route('products.index', ['sort' => 'bestsellers']) }}" class="shrink-0 rounded-md px-2.5 py-1 hover:bg-white/10">{{ __('ui.home.bestsellers') }}</a>
            <a href="{{ route('sell') }}" class="shrink-0 rounded-md px-2.5 py-1 font-semibold hover:bg-white/10">{{ __('commerce.become_vendor') }}</a>
            <a href="{{ route('help') }}" class="shrink-0 rounded-md px-2.5 py-1 hover:bg-white/10">{{ __('ui.footer.help') }}</a>
        </nav>
    </div>

    <div id="menu-mobile" class="border-b border-twende-line bg-white px-4 py-3 lg:hidden dark:border-white/10 dark:bg-twende-night" x-show="open" x-cloak>
        <a href="{{ $deliverHref }}" class="mb-3 flex items-center gap-2 text-sm font-semibold">
            <x-icon name="pin" class="h-4 w-4 text-twende-green" />
            {{ __('ui.store.deliver_label') }} {{ $deliveryPlace ?? 'Kinshasa, RDC' }}
        </a>
        <nav class="flex flex-col gap-1 text-sm">
            <a href="{{ route('home') }}" class="rounded-lg px-2 py-2">{{ __('ui.nav.home') }}</a>
            <a href="{{ route('promotions') }}" class="rounded-lg px-2 py-2">{{ __('ui.store.offers') }}</a>
            <a href="{{ route('products.index') }}" class="rounded-lg px-2 py-2">{{ __('ui.nav.products') }}</a>
            <a href="{{ route('shops.index') }}" class="rounded-lg px-2 py-2">{{ __('ui.store.suppliers') }}</a>
            <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="rounded-lg px-2 py-2">{{ __('ui.home.newest') }}</a>
            <a href="{{ route('products.index', ['sort' => 'bestsellers']) }}" class="rounded-lg px-2 py-2">{{ __('ui.home.bestsellers') }}</a>
            <a href="{{ route('categories.index') }}" class="rounded-lg px-2 py-2">{{ __('ui.store.all_categories') }}</a>
            <a href="{{ route('sell') }}" class="rounded-lg px-2 py-2 font-semibold">{{ __('commerce.become_vendor') }}</a>
            <a href="{{ route('help') }}" class="rounded-lg px-2 py-2">{{ __('ui.footer.help') }}</a>
            @auth
                <a href="{{ route('dashboard') }}" class="rounded-lg px-2 py-2 font-semibold">{{ __('ui.dashboard.greeting', ['name' => $given]) }} · {{ __('ui.nav.account') }}</a>
                <a href="{{ route('orders.index') }}" class="rounded-lg px-2 py-2">{{ __('commerce.orders') }}</a>
                <a href="{{ route('notifications.index') }}" class="rounded-lg px-2 py-2">{{ __('commerce.notifications') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-lg px-2 py-2 text-left font-semibold text-twende-red">{{ __('ui.nav.logout') }}</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="rounded-lg px-2 py-2 font-semibold">{{ __('ui.nav.login') }}</a>
                <a href="{{ route('register') }}" class="rounded-lg px-2 py-2 font-semibold text-twende-red">{{ __('ui.nav.register') }}</a>
            @endauth
        </nav>
        <div class="mt-3">
            <x-theme-toggle />
        </div>
    </div>
</header>
