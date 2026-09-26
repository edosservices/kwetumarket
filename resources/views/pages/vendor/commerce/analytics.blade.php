<x-layouts.dashboard :title="__('commerce.analytics')">
    <h1 class="text-2xl font-bold">{{ __('commerce.analytics') }}</h1>
    <dl class="mt-6 grid gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-twende-line p-4 dark:border-white/10"><dt class="text-sm text-twende-muted">{{ __('commerce.orders') }}</dt><dd class="text-2xl font-bold">{{ $orders }}</dd></div>
        <div class="rounded-2xl border border-twende-line p-4 dark:border-white/10"><dt class="text-sm text-twende-muted">{{ __('commerce.total') }}</dt><dd class="text-2xl font-bold">{{ \App\Support\Money::format($gross) }}</dd></div>
        <div class="rounded-2xl border border-twende-line p-4 dark:border-white/10"><dt class="text-sm text-twende-muted">{{ __('commerce.commission') }}</dt><dd class="text-2xl font-bold">{{ \App\Support\Money::format($commission) }}</dd></div>
        <div class="rounded-2xl border border-twende-line p-4 dark:border-white/10"><dt class="text-sm text-twende-muted">{{ __('commerce.balance') }}</dt><dd class="text-2xl font-bold">{{ \App\Support\Money::format($balance) }}</dd></div>
    </dl>
</x-layouts.dashboard>
