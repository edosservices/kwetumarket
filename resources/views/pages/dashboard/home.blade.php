<x-layouts.dashboard :title="__('ui.nav.dashboard')">
    <h1 class="text-2xl font-bold sm:text-3xl">{{ __('ui.dashboard.greeting', ['name' => $user->name]) }}</h1>
    <div class="mt-3">
        <x-badge variant="green">{{ $user->getRoleNames()->map(fn ($role) => __('ui.roles.'.$role))->join(', ') }}</x-badge>
    </div>

    @php
        $intro = match ($user->getRoleNames()->first()) {
            'vendor' => __('ui.dashboard.vendor_intro'),
            'delivery_agent' => __('ui.dashboard.delivery_intro'),
            'admin' => __('ui.dashboard.admin_intro'),
            default => __('ui.dashboard.client_intro'),
        };
        $modules = match ($user->getRoleNames()->first()) {
            'vendor' => ['shop', 'products', 'stock', 'orders', 'finance'],
            'delivery_agent' => ['missions', 'deliveries', 'finance'],
            'admin' => ['users', 'shop', 'products', 'orders', 'payments', 'deliveries', 'disputes', 'statistics', 'settings'],
            default => ['orders', 'addresses', 'wishlist', 'messages'],
        };
    @endphp

    <p class="mt-4 max-w-2xl text-sm leading-relaxed text-twende-muted">{{ $intro }}</p>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($modules as $module)
            <article class="rounded-2xl border border-twende-line bg-white p-5 dark:border-white/10 dark:bg-twende-night-card">
                <h2 class="font-semibold">{{ __('ui.dashboard.'.$module) }}</h2>
                <div class="mt-3">
                    <x-badge>{{ __('ui.dashboard.soon') }}</x-badge>
                </div>
            </article>
        @endforeach
    </div>
</x-layouts.dashboard>
