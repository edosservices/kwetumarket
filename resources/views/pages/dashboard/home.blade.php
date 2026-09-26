<x-layouts.dashboard :title="__('ui.nav.dashboard')">
    <h1 class="text-2xl font-bold sm:text-3xl">{{ __('ui.dashboard.greeting', ['name' => $user->name]) }}</h1>
    <div class="mt-3">
        <x-badge variant="green">{{ $user->getRoleNames()->map(fn ($role) => __('ui.roles.'.$role))->join(', ') }}</x-badge>
    </div>
    @php
        $links = match ($user->getRoleNames()->first()) {
            'vendor' => [
                __('ui.dashboard.shop') => route('vendor.shop.edit'),
                __('commerce.orders') => route('vendor.orders.index'),
                __('commerce.wallet') => route('vendor.wallet'),
                __('commerce.analytics') => route('vendor.analytics'),
            ],
            'delivery_agent' => [
                __('commerce.missions') => route('delivery.jobs'),
                __('commerce.earnings') => route('delivery.jobs'),
            ],
            'admin' => [
                __('commerce.orders') => route('admin.orders.index'),
                __('commerce.analytics') => route('admin.analytics'),
                __('commerce.settings') => route('admin.settings'),
                __('commerce.users') => route('admin.users.index'),
            ],
            default => [
                __('commerce.orders') => route('orders.index'),
                __('commerce.favorites') => route('favorites.index'),
                __('commerce.follows') => route('follows.index'),
                __('commerce.messages') => route('messages.index'),
                __('commerce.notifications') => route('notifications.index'),
                __('commerce.referral') => route('referral'),
            ],
        };
    @endphp
    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($links as $label => $href)
            <a href="{{ $href }}" class="rounded-2xl border border-twende-line bg-white p-5 hover:border-twende-red dark:border-white/10 dark:bg-twende-night-card">
                <h2 class="font-semibold">{{ $label }}</h2>
            </a>
        @endforeach
    </div>
</x-layouts.dashboard>
