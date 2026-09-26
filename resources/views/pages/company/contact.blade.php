<x-layouts.storefront :title="__('ui.footer.contact')">
    <article class="mx-auto max-w-3xl px-4 py-12">
        <h1 class="text-3xl font-bold">{{ __('ui.footer.contact') }}</h1>
        <p class="mt-4 leading-relaxed text-twende-muted">{{ __('ui.company.contact_body') }}</p>
        <p class="mt-6"><a class="font-semibold text-twende-red" href="mailto:{{ config('twende.contact_email') }}">{{ config('twende.contact_email') }}</a></p>
    </article>
</x-layouts.storefront>
