<x-layouts.storefront :title="__('ui.footer.about_link')">
    <article class="mx-auto max-w-3xl px-4 py-12">
        <h1 class="text-3xl font-bold">{{ config('twende.name') }}</h1>
        <p class="mt-4 text-base leading-relaxed text-twende-muted">{{ __('ui.company.about_body') }}</p>
        <p class="mt-8 text-sm text-twende-muted">© {{ now()->year }} {{ config('twende.name') }} — {{ __('ui.footer.developed_by', ['name' => config('twende.developer')]) }}</p>
    </article>
</x-layouts.storefront>
