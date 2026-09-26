<x-layouts.dashboard :title="__('commerce.orders')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('commerce.orders') }}</h1>
    @if ($orders->isEmpty())
        <div class="mt-6"><x-empty-state :title="__('commerce.orders_empty_title')" :description="__('commerce.orders_empty_body')"><x-slot:action><x-button :href="route('products.index')">{{ __('commerce.browse') }}</x-button></x-slot:action></x-empty-state></div>
    @else
        <div class="mt-6 overflow-x-auto">
            <table class="w-full min-w-[36rem] text-left text-sm">
                <thead class="text-twende-muted"><tr><th class="py-2">N°</th><th>Total</th><th>Statut</th><th>{{ __('commerce.tracking') }}</th></tr></thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr class="border-t border-twende-line dark:border-white/10">
                            <td class="py-3"><a class="font-semibold text-twende-red" href="{{ route('orders.show', $order) }}">{{ $order->number }}</a></td>
                            <td>{{ \App\Support\Money::format((int) $order->total, $order->currency) }}</td>
                            <td>{{ __('commerce.order_statuses.'.$order->status) }}</td>
                            <td>{{ __('commerce.delivery_statuses.'.($order->delivery->status ?? 'pending')) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
</x-layouts.dashboard>
