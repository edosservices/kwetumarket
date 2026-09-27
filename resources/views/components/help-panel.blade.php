<div
    class="fixed bottom-20 right-3 z-30 lg:bottom-6"
    x-data="{ open: false, hide: false }"
    x-init="const footer = document.querySelector('[data-site-footer]'); if (footer && 'IntersectionObserver' in window) { new IntersectionObserver(([entry]) => { hide = entry.isIntersecting; if (hide) open = false; }, { threshold: 0.15 }).observe(footer); }"
    x-show="open || ! hide"
    x-cloak
>
    <div
        x-show="open"
        x-transition.opacity
        class="mb-2 w-[min(18rem,calc(100vw-1.5rem))] rounded-lg border border-twende-line bg-white p-3 text-sm shadow-lg dark:border-white/10 dark:bg-twende-night-card"
        role="dialog"
        aria-label="{{ __('ui.market.help_need') }}"
    >
        <p class="font-bold">{{ __('ui.market.help_need') }}</p>
        <ul class="mt-2 space-y-1">
            <li><a href="{{ route('help') }}" class="font-semibold text-twende-green">{{ __('ui.store.help_center') }}</a></li>
            <li><a href="{{ route('faq') }}" class="font-semibold text-twende-green">{{ __('ui.footer.faq') }}</a></li>
            <li><a href="{{ route('contact') }}" class="font-semibold text-twende-green">{{ __('ui.footer.contact') }}</a></li>
            @if (filled(config('twende.contact_email')))
                <li><a href="mailto:{{ config('twende.contact_email') }}" class="break-all font-semibold text-twende-red">{{ config('twende.contact_email') }}</a></li>
            @endif
        </ul>
    </div>
    <button
        type="button"
        class="twende-press inline-flex h-10 items-center gap-2 rounded-full bg-twende-dark px-3 text-sm font-semibold text-white shadow-md"
        x-on:click="open = ! open"
        x-bind:aria-expanded="open.toString()"
    >
        <x-icon name="support" class="h-4 w-4" />
        <span x-show="! open">{{ __('ui.market.help_need') }}</span>
        <span x-show="open" x-cloak>{{ __('ui.nav.close') }}</span>
    </button>
</div>
