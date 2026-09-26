<x-layouts.dashboard :title="__('commerce.analytics')">
    <h1 class="text-2xl font-bold">{{ __('commerce.analytics') }}</h1>
    <dl class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach (['orders' => $orders, 'revenue' => \App\Support\Money::format($revenue), 'users' => $users, 'products' => $products, 'disputes' => $openDisputes] as $label => $value)
            <div class="rounded-2xl border border-twende-line p-4 dark:border-white/10"><dt class="text-sm text-twende-muted">{{ $label }}</dt><dd class="text-2xl font-bold">{{ $value }}</dd></div>
        @endforeach
    </dl>
</x-layouts.dashboard>
