<x-layouts.dashboard :title="__('ui.dashboard.clients')">
    <h1 class="text-xl font-bold">{{ __('ui.dashboard.clients') }}</h1>
    @if ($customers->isEmpty())
        <div class="mt-4"><x-empty-state :title="__('ui.dashboard.no_sales')" /></div>
    @else
        <ul class="mt-4 divide-y divide-twende-line overflow-hidden rounded-lg border border-twende-line bg-white dark:divide-white/10 dark:border-white/10 dark:bg-twende-night-card">
            @foreach ($customers as $customer)
                <li class="flex items-center justify-between gap-3 px-3 py-2 text-sm">
                    <span class="min-w-0 truncate font-medium">{{ $customer->name }}</span>
                    <span class="shrink-0 text-twende-muted">{{ trans_choice('ui.store.order_count', (int) $customer->vendor_orders_count, ['count' => (int) $customer->vendor_orders_count]) }}</span>
                </li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $customers->links() }}</div>
    @endif
</x-layouts.dashboard>
