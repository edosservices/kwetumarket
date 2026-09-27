@php
    $seller = auth()->check() && auth()->user()->hasAnyRole(['vendor', 'admin']);
    $buyer = auth()->check();
    $canOrders = (bool) auth()->user()?->can('orders.view-own');
    $canWishlist = (bool) auth()->user()?->can('wishlist.manage');
    $columns = [
        [
            'title' => config('twende.name'),
            'links' => [
                ['label' => __('ui.market.about_twende'), 'href' => route('about')],
                ['label' => __('ui.market.our_categories'), 'href' => route('categories.index')],
                ['label' => __('ui.market.our_shops'), 'href' => route('shops.index')],
                ['label' => __('commerce.become_vendor'), 'href' => route('sell')],
                ['label' => __('ui.store.seller_center'), 'href' => route('sell')],
                ['label' => __('ui.market.careers'), 'href' => null, 'note' => __('ui.market.careers_note')],
            ],
        ],
        [
            'title' => __('ui.market.col_buyers'),
            'links' => [
                ['label' => __('ui.market.how_to_buy'), 'href' => route('help')],
                ['label' => __('ui.market.my_account'), 'href' => $buyer ? route('dashboard') : route('login')],
                ['label' => __('commerce.orders'), 'href' => $canOrders ? route('orders.index') : ($buyer ? route('dashboard') : route('login'))],
                ['label' => __('commerce.favorites'), 'href' => $canWishlist ? route('favorites.index') : ($buyer ? route('dashboard') : route('login'))],
                ['label' => __('ui.nav.cart'), 'href' => route('cart.show')],
                ['label' => __('commerce.delivery'), 'href' => route('help')],
                ['label' => __('commerce.payment'), 'href' => route('help')],
                ['label' => __('ui.market.returns_refunds'), 'href' => route('help')],
            ],
        ],
        [
            'title' => __('ui.market.col_sellers'),
            'links' => [
                ['label' => __('commerce.become_vendor'), 'href' => route('sell')],
                ['label' => __('ui.market.vendor_dashboard'), 'href' => $seller ? route('vendor.dashboard') : route('sell')],
                ['label' => __('ui.market.add_product'), 'href' => $seller ? route('vendor.products.create') : route('sell')],
                ['label' => __('ui.market.manage_products'), 'href' => $seller ? route('vendor.products.index') : route('sell')],
                ['label' => __('ui.market.manage_orders'), 'href' => $seller ? route('vendor.orders.index') : route('sell')],
                ['label' => __('ui.dashboard.clients'), 'href' => $seller ? route('vendor.customers') : route('sell')],
                ['label' => __('commerce.dropship'), 'href' => $seller ? route('vendor.dropship.index') : route('sell')],
                ['label' => __('ui.store.seller_center'), 'href' => route('sell')],
            ],
        ],
        [
            'title' => __('ui.market.col_safety'),
            'links' => [
                ['label' => __('ui.store.help_center'), 'href' => route('help')],
                ['label' => __('ui.footer.contact'), 'href' => route('contact')],
                ['label' => __('ui.dashboard.security_title'), 'href' => $buyer ? route('profile.edit').'#securite' : route('login')],
                ['label' => __('ui.footer.terms'), 'href' => route('terms')],
                ['label' => __('ui.footer.privacy'), 'href' => route('privacy')],
                ['label' => __('ui.store.returns'), 'href' => route('help')],
                ['label' => __('ui.market.report'), 'href' => route('contact')],
            ],
        ],
    ];
    $trust = [
        ['icon' => 'lock', 'label' => __('ui.market.trust_pay')],
        ['icon' => 'truck', 'label' => __('ui.market.trust_ship')],
        ['icon' => 'return', 'label' => __('ui.market.trust_returns')],
        ['icon' => 'shield', 'label' => __('ui.market.trust_protect')],
        ['icon' => 'users', 'label' => __('ui.market.trust_sellers')],
    ];
@endphp

<section class="border-t border-twende-line bg-white dark:border-white/10 dark:bg-twende-night" aria-labelledby="why-twende">
    <div class="mx-auto max-w-[100rem] px-3 py-4 sm:px-4">
        <h2 id="why-twende" class="text-sm font-bold">{{ __('ui.market.why_title') }}</h2>
        <ul class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ([
                ['icon' => 'lock', 'title' => __('ui.market.why_pay'), 'body' => __('ui.market.why_pay_body')],
                ['icon' => 'truck', 'title' => __('ui.market.why_ship'), 'body' => __('ui.market.why_ship_body')],
                ['icon' => 'shield', 'title' => __('ui.market.why_protect'), 'body' => __('ui.market.why_protect_body')],
                ['icon' => 'users', 'title' => __('ui.market.why_sellers'), 'body' => __('ui.market.why_sellers_body')],
                ['icon' => 'support', 'title' => __('ui.market.why_support'), 'body' => __('ui.market.why_support_body')],
            ] as $item)
                <li class="twende-lift rounded-lg border border-twende-line bg-twende-light p-3 dark:border-white/10 dark:bg-white/5">
                    <x-icon :name="$item['icon']" class="h-5 w-5 text-twende-green" />
                    <p class="mt-2 text-sm font-semibold">{{ $item['title'] }}</p>
                    <p class="mt-1 text-xs leading-relaxed text-twende-muted">{{ $item['body'] }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>

<footer data-site-footer class="bg-twende-night pb-24 text-white lg:pb-0">
    <div class="border-b border-white/10">
        <a href="#contenu" class="twende-press block py-3 text-center text-sm font-semibold hover:bg-white/5">{{ __('ui.store.back_to_top') }}</a>
    </div>

    <div class="border-b border-white/10">
        <ul class="mx-auto grid max-w-[100rem] grid-cols-2 gap-3 px-3 py-4 sm:grid-cols-3 sm:px-4 lg:grid-cols-5">
            @foreach ($trust as $item)
                <li class="flex items-center gap-2 text-sm font-semibold text-white/90">
                    <x-icon :name="$item['icon']" class="h-5 w-5 shrink-0 text-twende-green-bright" />
                    <span>{{ $item['label'] }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="mx-auto grid max-w-[100rem] gap-2 px-3 py-6 sm:px-4 md:grid-cols-2 md:gap-8 lg:grid-cols-4">
        @foreach ($columns as $column)
            <details open class="border-b border-white/10 pb-2 md:border-0 md:pb-0" x-data="{}" x-init="if (window.matchMedia('(max-width: 767px)').matches) $el.removeAttribute('open')">
                <summary class="flex cursor-pointer list-none items-center justify-between py-2 text-sm font-bold md:pointer-events-none md:py-0">
                    {{ $column['title'] }}
                    <span class="text-white/50 md:hidden" aria-hidden="true">+</span>
                </summary>
                <ul class="mt-2 space-y-1.5 pb-2 text-sm text-white/75">
                    @foreach ($column['links'] as $link)
                        <li>
                            @if ($link['href'])
                                <a href="{{ $link['href'] }}" class="twende-nudge inline-flex rounded-sm hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70">{{ $link['label'] }}</a>
                            @else
                                <span>{{ $link['label'] }}</span>
                                @if (! empty($link['note']))
                                    <span class="mt-0.5 block text-xs text-white/45">{{ $link['note'] }}</span>
                                @endif
                            @endif
                        </li>
                    @endforeach
                </ul>
            </details>
        @endforeach
    </div>

    <div class="border-t border-white/10">
        <div class="mx-auto grid max-w-[100rem] gap-4 px-3 py-5 sm:px-4 md:grid-cols-2">
            <div>
                <p class="text-sm font-bold">{{ __('ui.market.app_title') }}</p>
                <p class="mt-1 max-w-md text-sm text-white/70">{{ __('ui.market.app_body') }}</p>
                <p class="mt-3 inline-flex h-9 items-center rounded-full border border-white/20 px-3 text-xs font-semibold text-white/70" aria-disabled="true">{{ __('ui.market.app_soon') }}</p>
            </div>
            <div>
                <p class="text-sm font-bold">{{ __('ui.market.help_need') }}</p>
                <ul class="mt-2 space-y-1 text-sm text-white/75">
                    <li><a href="{{ route('help') }}" class="hover:text-white">{{ __('ui.store.help_center') }}</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-white">{{ __('ui.footer.contact') }}</a></li>
                    <li><a href="{{ route('faq') }}" class="hover:text-white">{{ __('ui.footer.faq') }}</a></li>
                    @if (filled(config('twende.contact_email')))
                        <li><a href="mailto:{{ config('twende.contact_email') }}" class="hover:text-white">{{ config('twende.contact_email') }}</a></li>
                    @endif
                </ul>
                <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-white/50">{{ __('ui.footer.language') }}</p>
                <ul class="mt-2 flex flex-wrap gap-2">
                    @foreach (config('twende.locales') as $locale)
                        <li>
                            <a
                                href="{{ route('locale.switch', $locale) }}"
                                hreflang="{{ $locale }}"
                                lang="{{ $locale }}"
                                class="inline-flex h-7 items-center rounded-full px-2.5 text-[11px] font-semibold uppercase {{ app()->getLocale() === $locale ? 'bg-twende-red text-white' : 'bg-white/10 text-white' }}"
                            >{{ $locale }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-[100rem] flex-col gap-2 px-3 py-4 text-center text-xs text-white/70 sm:px-4 sm:text-left">
            <p>© {{ now()->year }} {{ config('twende.name') }} — {{ __('ui.footer.rights') }}</p>
            <p>{{ __('ui.footer.developed_by', ['name' => config('twende.developer')]) }}</p>
            <p class="text-sm font-semibold tracking-wide text-white">{{ __('ui.market.designed_by') }}</p>
            <ul class="flex flex-wrap justify-center gap-x-4 gap-y-1 sm:justify-start">
                <li><a href="{{ route('terms') }}" class="hover:text-white">{{ __('ui.footer.terms') }}</a></li>
                <li><a href="{{ route('privacy') }}" class="hover:text-white">{{ __('ui.footer.privacy') }}</a></li>
                <li><a href="{{ route('privacy') }}" class="hover:text-white">{{ __('ui.store.cookies') }}</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-white">{{ __('ui.footer.contact') }}</a></li>
            </ul>
            <p class="text-[11px] text-white/45">{{ __('ui.footer.currency_note') }}</p>
        </div>
    </div>
</footer>
