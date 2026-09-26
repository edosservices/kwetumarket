<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-twende-line bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur lg:hidden dark:border-white/10 dark:bg-twende-night/95" aria-label="{{ __('experience.home') }}">
    <ul class="mx-auto grid max-w-lg grid-cols-5">
        @foreach ([
            ['route' => 'home', 'active' => request()->routeIs('home'), 'icon' => 'home', 'label' => __('experience.home'), 'class' => 'text-twende-red'],
            ['route' => 'search', 'active' => request()->routeIs('search', 'search.image.show'), 'icon' => 'search', 'label' => __('experience.search'), 'class' => ''],
            ['route' => 'categories.index', 'active' => request()->routeIs('categories.*'), 'icon' => 'grid', 'label' => __('experience.categories'), 'class' => ''],
            ['route' => 'cart.show', 'active' => request()->routeIs('cart.*'), 'icon' => 'cart', 'label' => __('experience.cart'), 'class' => 'text-twende-green-bright', 'count' => $cartCount ?? 0],
            ['route' => auth()->check() ? 'dashboard' : 'login', 'active' => request()->routeIs('dashboard', 'profile.*', 'login'), 'icon' => 'user', 'label' => __('experience.account'), 'class' => '', 'count' => $unreadNotifications ?? 0],
        ] as $item)
            <li>
                <a href="{{ route($item['route']) }}" @class([
                    'relative flex min-h-14 flex-col items-center justify-center gap-0.5 px-1 text-[11px] font-semibold',
                    'text-twende-red' => $item['active'],
                    'text-twende-muted' => ! $item['active'],
                ]) @if ($item['active']) aria-current="page" @endif>
                    <x-icon :name="$item['icon']" @class(['h-5 w-5', $item['class']]) />
                    <span>{{ $item['label'] }}</span>
                    @if (($item['count'] ?? 0) > 0)
                        <span class="absolute right-3 top-1 inline-flex min-w-4 items-center justify-center rounded-full bg-twende-red px-1 text-[10px] font-bold text-white">{{ $item['count'] }}</span>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
</nav>
