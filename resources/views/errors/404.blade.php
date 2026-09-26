<x-layouts.storefront :title="__('commerce.not_found')">
    <div class="mx-auto max-w-lg px-4 py-16 text-center">
        <x-brand-logo size="md" class="mx-auto" />
        <h1 class="mt-6 text-3xl font-bold">{{ __('commerce.not_found') }}</h1>
        <p class="mt-3 text-sm text-twende-muted">{{ __('commerce.error_body') }}</p>
        <x-button :href="route('home')" class="mt-6">{{ __('commerce.back_home') }}</x-button>
    </div>
</x-layouts.storefront>
