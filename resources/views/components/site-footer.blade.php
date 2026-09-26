<footer class="mt-auto border-t border-twende-line bg-twende-light dark:border-white/10 dark:bg-twende-night-card">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:grid-cols-2 lg:grid-cols-4">
        <div class="sm:col-span-2">
            <x-brand-logo size="md" :href="route('home')" />
            <p class="mt-4 max-w-md text-sm leading-relaxed text-twende-muted">{{ __('ui.footer.about') }}</p>
        </div>
        <div>
            <p class="text-sm font-semibold">{{ __('ui.nav.home') }}</p>
            <ul class="mt-3 space-y-2 text-sm">
                <li><a href="{{ route('categories.index') }}" class="hover:text-twende-red">{{ __('ui.nav.categories') }}</a></li>
                <li><a href="{{ route('shops.index') }}" class="hover:text-twende-red">{{ __('ui.nav.shops') }}</a></li>
                <li><a href="{{ route('search') }}" class="hover:text-twende-red">{{ __('ui.nav.search') }}</a></li>
                <li><a href="{{ route('register') }}" class="hover:text-twende-red">{{ __('ui.nav.register') }}</a></li>
            </ul>
        </div>
        <div>
            <p class="text-sm font-semibold">{{ __('ui.footer.language') }}</p>
            <ul class="mt-3 flex flex-wrap gap-2">
                @foreach (config('twende.locales') as $locale)
                    <li>
                        <a
                            href="{{ route('locale.switch', $locale) }}"
                            hreflang="{{ $locale }}"
                            lang="{{ $locale }}"
                            class="inline-flex h-8 items-center rounded-full px-3 text-xs font-semibold uppercase {{ app()->getLocale() === $locale ? 'bg-twende-red text-white' : 'bg-white text-twende-dark dark:bg-white/10 dark:text-white' }}"
                        >{{ $locale }}</a>
                    </li>
                @endforeach
            </ul>
            <p class="mt-4 text-sm font-semibold">{{ __('ui.footer.currencies') }}</p>
            <p class="mt-2 text-sm text-twende-muted">{{ __('ui.footer.currency_note') }}</p>
        </div>
    </div>
    <div class="border-t border-twende-line px-4 py-4 text-center text-xs text-twende-muted dark:border-white/10">
        © {{ now()->year }} {{ config('twende.name') }}. {{ __('ui.footer.rights') }}
    </div>
</footer>
