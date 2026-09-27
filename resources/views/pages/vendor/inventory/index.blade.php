<x-layouts.dashboard :title="__('ui.catalog.stock')">
    @if (session('status'))
        <x-alert variant="success" class="mb-4">{{ session('status') }}</x-alert>
    @endif
    <h1 class="text-2xl font-bold">{{ __('ui.catalog.stock') }}</h1>
    @if ($alerts->isNotEmpty())
        <ul class="mt-4 space-y-2">
            @foreach ($alerts as $alert)
                <li class="rounded-xl border border-twende-line px-4 py-3 text-sm dark:border-white/10">
                    <strong>{{ $alert->available() === 0 ? __('operations.stock_out_title') : __('operations.stock_low_title') }}</strong>
                    — {{ $alert->product->name }} @if($alert->variant) · {{ $alert->variant->name }} @endif · {{ $alert->available() }}
                </li>
            @endforeach
        </ul>
    @endif
    <form method="POST" action="{{ route('vendor.inventory.store') }}" class="mt-6 grid max-w-3xl gap-4 sm:grid-cols-2">
        @csrf
        <x-select name="product_id" :label="__('ui.catalog.products_title')" :selected="old('product_id')" :options="$products->pluck('name', 'id')->all()" />
        <x-select name="type" :label="__('ui.catalog.movement')" :selected="old('type', 'purchase')" :options="[
            'purchase' => __('ui.catalog.movements.purchase'),
            'sale' => __('ui.catalog.movements.sale'),
            'return' => __('ui.catalog.movements.return'),
            'adjustment' => __('ui.catalog.movements.adjustment'),
            'reservation' => __('ui.catalog.movements.reservation'),
            'release' => __('ui.catalog.movements.release'),
            'cancellation' => __('ui.catalog.movements.cancellation'),
        ]" />
        <x-input name="quantity" type="number" :label="__('ui.catalog.quantity')" :value="old('quantity', 1)" />
        <x-input name="reference" :label="__('ui.catalog.reference')" :value="old('reference')" />
        <div class="sm:col-span-2">
            <x-textarea name="comment" :label="__('ui.catalog.comment')" :value="old('comment')" />
        </div>
        <x-button type="submit" size="sm">{{ __('ui.catalog.save') }}</x-button>
    </form>
    <div class="mt-8 overflow-x-auto rounded-2xl border border-twende-line dark:border-white/10">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-twende-light text-twende-muted dark:bg-white/5">
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('ui.catalog.products_title') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('ui.catalog.movement') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('ui.catalog.quantity') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('ui.catalog.before') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('ui.catalog.after') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($movements as $movement)
                    <tr class="border-t border-twende-line dark:border-white/10">
                        <td class="px-4 py-3">{{ $movement->product->name }}@if($movement->variant) · {{ $movement->variant->name }}@endif</td>
                        <td class="px-4 py-3">{{ __('ui.catalog.movements.'.$movement->type->value) }}</td>
                        <td class="px-4 py-3">{{ $movement->quantity }}</td>
                        <td class="px-4 py-3">{{ $movement->quantity_before }}</td>
                        <td class="px-4 py-3">{{ $movement->quantity_after }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-twende-muted">{{ __('ui.catalog.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6"><x-pagination :paginator="$movements" /></div>
</x-layouts.dashboard>
