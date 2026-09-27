<x-layouts.admin :title="$order->number">
    <h1 class="text-2xl font-bold">{{ $order->number }}</h1>
    <p class="mt-2 text-sm text-twende-muted">{{ $order->status }} · {{ \App\Support\Money::format((int) $order->total_minor, $order->currency) }}</p>
    @if ($options !== [])
        <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="mt-6 max-w-xl space-y-4">
            @csrf
            @method('PUT')
            <x-select name="status" :label="__('ui.fields.status')" :options="collect($options)->mapWithKeys(fn ($permission, $status) => [$status => __('ui.orders.'.$status)])->all()" />
            <x-button type="submit">{{ __('ui.actions.save') }}</x-button>
        </form>
    @endif
</x-layouts.admin>
