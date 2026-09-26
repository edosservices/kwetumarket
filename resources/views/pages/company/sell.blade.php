<x-layouts.storefront :title="__('ui.footer.sell')">
    <article class="mx-auto max-w-3xl px-4 py-12">
        <h1 class="text-3xl font-bold">{{ __('ui.footer.sell') }}</h1>
        <p class="mt-4 leading-relaxed text-twende-muted">{{ __('ui.company.sell_body') }}</p>
        <div class="mt-6">
            <x-button :href="route('register')">{{ __('ui.nav.register') }}</x-button>
        </div>
    </article>
</x-layouts.storefront>
