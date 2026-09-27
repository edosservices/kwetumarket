<x-layouts.dashboard :title="__('commerce.orders')">
    <x-flash />
    <h1 class="text-xl font-bold sm:text-2xl">{{ __('commerce.orders') }}</h1>
    @if ($orders->isEmpty())
        <div class="mt-4"><x-empty-state :title="__('commerce.orders_empty_title')" :description="__('commerce.orders_empty_body')"><x-slot:action><x-button :href="route('products.index')">{{ __('commerce.browse') }}</x-button></x-slot:action></x-empty-state></div>
    @else
        <ul class="mt-4 grid gap-2">
            @foreach ($orders as $order)
                <li class="rounded-lg border border-twende-line bg-white p-3 dark:border-white/10 dark:bg-twende-night-card">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <a class="font-semibold text-twende-red" href="{{ route('orders.show', $order) }}">{{ $order->number }}</a>
                        <span class="text-sm font-semibold">{{ \App\Support\Money::format((int) $order->total, $order->currency) }}</span>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <x-badge variant="green">{{ __('commerce.order_statuses.'.$order->status) }}</x-badge>
                        <x-badge>{{ __('commerce.delivery_statuses.'.($order->delivery->status ?? 'pending')) }}</x-badge>
                    </div>
                </li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
</x-layouts.dashboard>
