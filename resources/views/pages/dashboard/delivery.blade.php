<x-layouts.dashboard :title="__('ui.nav.delivery')">
    <x-brand-logo size="sm" class="mb-6" />
    <h1 class="text-2xl font-bold">{{ __('ui.nav.delivery') }}</h1>
    <p class="mt-3 max-w-2xl text-sm leading-relaxed text-twende-muted">{{ __('ui.dashboard.delivery_intro') }}</p>
    <div class="mt-8 grid gap-4 sm:grid-cols-2">
        <a href="{{ route('delivery.jobs') }}" class="rounded-2xl border border-twende-line p-5 hover:border-twende-green dark:border-white/10">
            <h2 class="font-semibold">{{ __('commerce.missions') }}</h2>
        </a>
        <a href="{{ route('delivery.jobs') }}" class="rounded-2xl border border-twende-line p-5 hover:border-twende-green dark:border-white/10">
            <h2 class="font-semibold">{{ __('commerce.earnings') }}</h2>
        </a>
    </div>
</x-layouts.dashboard>
