<x-layouts.storefront :title="__('ui.footer.faq')">
    <article class="mx-auto max-w-3xl px-4 py-12">
        <h1 class="text-3xl font-bold">{{ __('ui.footer.faq') }}</h1>
        <div class="mt-6 space-y-6">
            <section>
                <h2 class="font-semibold">{{ __('ui.company.faq_image_q') }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-twende-muted">{{ __('ui.company.faq_image_a') }}</p>
            </section>
            <section>
                <h2 class="font-semibold">{{ __('ui.company.faq_location_q') }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-twende-muted">{{ __('ui.company.faq_location_a') }}</p>
            </section>
            <section>
                <h2 class="font-semibold">{{ __('ui.company.faq_order_q') }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-twende-muted">{{ __('ui.company.faq_order_a') }}</p>
            </section>
        </div>
    </article>
</x-layouts.storefront>
