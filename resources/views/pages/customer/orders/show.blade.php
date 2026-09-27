<x-layouts.customer :title="$order->number">
    <h1 class="text-2xl font-bold">{{ $order->number }}</h1>
    <p class="mt-2 text-sm text-twende-muted">{{ $order->status }} · {{ \App\Support\Money::format((int) $order->total_minor, $order->currency) }}</p>
    <ul class="mt-6 space-y-2">
        @foreach ($order->items as $item)
            <li class="rounded-2xl border border-twende-line px-4 py-3 dark:border-white/10">{{ $item->product?->name }} × {{ $item->quantity }}</li>
        @endforeach
    </ul>
</x-layouts.customer>
