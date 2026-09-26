<x-layouts.dashboard :title="__('commerce.orders')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('commerce.orders') }}</h1>
    <div class="mt-6 overflow-x-auto">
        <table class="w-full min-w-[40rem] text-left text-sm">
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-t border-twende-line dark:border-white/10">
                        <td class="py-3 font-semibold">{{ $order->number }}</td>
                        <td>{{ $order->user?->name }}</td>
                        <td>{{ __('commerce.order_statuses.'.$order->status) }}</td>
                        <td>
                            @foreach ($order->items as $item)
                                <span class="block">{{ $item->name }} × {{ $item->quantity }}</span>
                            @endforeach
                        </td>
                        <td>
                            @if ($order->status === 'confirmed')
                                <form method="POST" action="{{ route('vendor.orders.prepare', $order) }}">@csrf<button class="text-sm font-semibold text-twende-green">{{ __('commerce.prepare') }}</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td class="py-6"><x-empty-state :title="__('commerce.orders_empty_title')" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.dashboard>
