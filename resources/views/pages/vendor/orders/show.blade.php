<x-layouts.vendor :title="$order->number">
    <h1 class="text-2xl font-bold">{{ $order->number }}</h1>
    <p class="mt-2 text-sm text-twende-muted">{{ $order->status }}</p>
    @if ($options !== [])
        <form method="POST" action="{{ route('vendor.orders.update', $order) }}" class="mt-6 max-w-xl space-y-4">
            @csrf
            @method('PUT')
            <x-select name="status" :label="__('ui.fields.status')" :options="collect($options)->mapWithKeys(fn ($permission, $status) => [$status => __('ui.orders.'.$status)])->all()" />
            <x-button type="submit">{{ __('ui.actions.save') }}</x-button>
        </form>
    @endif
    <ul class="mt-6 space-y-2">
        @foreach ($order->items as $item)
            <li class="rounded-2xl border border-twende-line px-4 py-3 dark:border-white/10">{{ $item->product?->name }} × {{ $item->quantity }}</li>
        @endforeach
    </ul>
</x-layouts.vendor>
