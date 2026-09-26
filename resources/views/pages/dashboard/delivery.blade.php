<x-layouts.dashboard :title="__('ui.nav.delivery')">
    <x-brand-logo size="sm" class="mb-6" />
    <h1 class="text-2xl font-bold">{{ __('ui.nav.delivery') }}</h1>
    <p class="mt-3 max-w-2xl text-sm leading-relaxed text-twende-muted">{{ __('ui.dashboard.delivery_intro') }}</p>
    <div class="mt-8 grid gap-4 sm:grid-cols-2">
        @foreach (['missions', 'deliveries', 'finance'] as $module)
            <article class="rounded-2xl border border-twende-line p-5 dark:border-white/10">
                <h2 class="font-semibold">{{ __('ui.dashboard.'.$module) }}</h2>
                <x-badge class="mt-3">{{ __('ui.dashboard.soon') }}</x-badge>
            </article>
        @endforeach
    </div>
</x-layouts.dashboard>
