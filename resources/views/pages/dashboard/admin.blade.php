<x-layouts.dashboard :title="__('ui.nav.admin')">
    <x-brand-logo size="sm" class="mb-6" />
    <h1 class="text-2xl font-bold">{{ __('ui.nav.admin') }}</h1>
    <p class="mt-3 max-w-2xl text-sm leading-relaxed text-twende-muted">{{ __('ui.dashboard.admin_intro') }}</p>
    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach (['users', 'shop', 'products', 'orders', 'payments', 'deliveries', 'disputes', 'statistics', 'settings'] as $module)
            <article class="rounded-2xl border border-twende-line p-5 dark:border-white/10">
                <h2 class="font-semibold">{{ __('ui.dashboard.'.$module) }}</h2>
                <x-badge variant="red" class="mt-3">{{ __('ui.dashboard.soon') }}</x-badge>
            </article>
        @endforeach
    </div>
</x-layouts.dashboard>
