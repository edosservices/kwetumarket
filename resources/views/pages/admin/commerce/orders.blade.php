<x-layouts.dashboard :title="__('commerce.orders')">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <h1 class="text-xl font-bold">{{ __('commerce.orders') }}</h1>
        <form method="GET" class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
            <label class="sr-only" for="admin-order-q">{{ __('ui.nav.search') }}</label>
            <input id="admin-order-q" name="q" value="{{ $term }}" placeholder="{{ __('ui.nav.search') }}" class="h-9 w-full rounded-md border border-twende-line bg-white px-3 text-sm sm:w-52 dark:border-white/15 dark:bg-twende-night">
            <label class="sr-only" for="admin-order-status">{{ __('ui.catalog.status') }}</label>
            <select id="admin-order-status" name="status" class="h-9 rounded-md border border-twende-line bg-white px-2 text-sm dark:border-white/15 dark:bg-twende-night" onchange="this.form.requestSubmit()">
                <option value="">{{ __('ui.catalog.all') }}</option>
                @foreach (__('commerce.order_statuses') as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>
    @if ($orders->isEmpty())
        <div class="mt-4"><x-empty-state :title="__('commerce.orders_empty_title')" /></div>
    @else
        <div class="mt-4 overflow-x-auto rounded-lg border border-twende-line bg-white dark:border-white/10 dark:bg-twende-night-card">
            <table class="min-w-[36rem]">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>{{ __('commerce.users') }}</th>
                        <th>{{ __('commerce.total') }}</th>
                        <th>{{ __('ui.catalog.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td><a class="font-semibold text-twende-red" href="{{ route('orders.show', $order) }}">{{ $order->number }}</a></td>
                            <td>{{ $order->user?->name }}</td>
                            <td>{{ \App\Support\Money::format((int) $order->total, $order->currency) }}</td>
                            <td><x-badge>{{ $order->payment_status }}</x-badge></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
</x-layouts.dashboard>
