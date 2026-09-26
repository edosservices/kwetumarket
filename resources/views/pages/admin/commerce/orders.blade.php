<x-layouts.dashboard :title="__('commerce.orders')">
    <h1 class="text-2xl font-bold">{{ __('commerce.orders') }}</h1>
    <div class="mt-6 overflow-x-auto">
        <table class="w-full min-w-[36rem] text-sm">
            @foreach ($orders as $order)
                <tr class="border-t border-twende-line dark:border-white/10">
                    <td class="py-2"><a class="font-semibold" href="{{ route('orders.show', $order) }}">{{ $order->number }}</a></td>
                    <td>{{ $order->user?->name }}</td>
                    <td>{{ \App\Support\Money::format((int) $order->total, $order->currency) }}</td>
                    <td>{{ $order->payment_status }}</td>
                </tr>
            @endforeach
        </table>
    </div>
</x-layouts.dashboard>
