<footer class="mt-auto bg-twende-night text-white">
    <div class="border-b border-white/10">
        <a href="#contenu" class="block py-3 text-center text-sm font-semibold hover:bg-white/5">{{ __('ui.store.back_to_top') }}</a>
    </div>
    <div class="mx-auto grid max-w-[100rem] gap-8 px-4 py-10 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <p class="text-sm font-bold">{{ config('twende.name') }}</p>
            <ul class="mt-3 space-y-2 text-sm text-white/75">
                <li><a href="{{ route('about') }}" class="hover:text-white">{{ __('ui.footer.about_link') }}</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-white">{{ __('ui.footer.contact') }}</a></li>
                <li><a href="{{ route('help') }}" class="hover:text-white">{{ __('ui.store.help_center') }}</a></li>
            </ul>
            <p class="mt-4 max-w-xs text-xs leading-relaxed text-white/60">{{ __('ui.footer.about') }}</p>
        </div>
        <div>
            <p class="text-sm font-bold">{{ __('ui.store.buy') }}</p>
            <ul class="mt-3 space-y-2 text-sm text-white/75">
                <li><a href="{{ route('categories.index') }}" class="hover:text-white">{{ __('ui.nav.categories') }}</a></li>
                <li><a href="{{ route('promotions') }}" class="hover:text-white">{{ __('ui.store.offers') }}</a></li>
                <li><a href="{{ route('products.index', ['sort' => 'bestsellers']) }}" class="hover:text-white">{{ __('ui.home.bestsellers') }}</a></li>
                <li><a href="{{ route('products.index') }}" class="hover:text-white">{{ __('ui.nav.products') }}</a></li>
                <li><a href="{{ route('shops.index') }}" class="hover:text-white">{{ __('ui.nav.shops') }}</a></li>
            </ul>
        </div>
        <div>
            <p class="text-sm font-bold">{{ __('ui.store.sell') }}</p>
            <ul class="mt-3 space-y-2 text-sm text-white/75">
                <li><a href="{{ route('sell') }}" class="hover:text-white">{{ __('commerce.become_vendor') }}</a></li>
                <li><a href="{{ route('sell') }}" class="hover:text-white">{{ __('ui.store.seller_center') }}</a></li>
                <li><a href="{{ route('terms') }}" class="hover:text-white">{{ __('ui.store.vendor_terms') }}</a></li>
            </ul>
        </div>
        <div>
            <p class="text-sm font-bold">{{ __('ui.footer.help') }}</p>
            <ul class="mt-3 space-y-2 text-sm text-white/75">
                <li><a href="{{ route('nearby') }}" class="hover:text-white">{{ __('commerce.delivery') }}</a></li>
                <li><a href="{{ route('help') }}" class="hover:text-white">{{ __('ui.store.returns') }}</a></li>
                <li><a href="{{ route('help') }}" class="hover:text-white">{{ __('ui.store.payments') }}</a></li>
                <li><a href="{{ route('faq') }}" class="hover:text-white">{{ __('ui.footer.faq') }}</a></li>
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
    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-[100rem] flex-col items-center gap-3 px-4 py-4 text-center text-xs text-white/70 sm:flex-row sm:justify-between sm:text-left">
            <p>© {{ now()->year }} {{ config('twende.name') }} — {{ __('ui.footer.developed_by', ['name' => config('twende.developer')]) }}</p>
            <ul class="flex flex-wrap justify-center gap-x-4 gap-y-1">
                <li><a href="{{ route('terms') }}" class="hover:text-white">{{ __('ui.footer.terms') }}</a></li>
                <li><a href="{{ route('privacy') }}" class="hover:text-white">{{ __('ui.footer.privacy') }}</a></li>
                <li><a href="{{ route('privacy') }}" class="hover:text-white">{{ __('ui.store.cookies') }}</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-white">{{ __('ui.footer.contact') }}</a></li>
            </ul>
        </div>
        <p class="px-4 pb-4 text-center text-[11px] text-white/45">{{ __('ui.footer.currency_note') }}</p>
    </div>
</footer>
