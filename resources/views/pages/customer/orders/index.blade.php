<x-layouts.customer :title="__('ui.modules.orders')">
    <h1 class="text-2xl font-bold">{{ __('ui.modules.orders') }}</h1>
    <div class="mt-6 grid gap-3">
        @forelse ($orders as $order)
            <a href="{{ route('customer.orders.show', $order) }}" class="rounded-2xl border border-twende-line px-4 py-3 dark:border-white/10">
                <span class="font-semibold">{{ $order->number }}</span>
                <span class="mt-1 block text-sm text-twende-muted">{{ $order->status }}</span>
            </a>
        @empty
            <x-empty-state :title="__('ui.modules.empty')" />
        @endforelse
    </div>
    <div class="mt-6">
        <x-pagination :paginator="$orders" />
    </div>
</x-layouts.customer>
