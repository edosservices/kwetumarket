<x-layouts.dashboard :title="__('ui.catalog.my_products')">
    @if (session('status'))
        <x-alert variant="success" class="mb-4">{{ session('status') }}</x-alert>
    @endif
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">{{ __('ui.catalog.my_products') }}</h1>
        <x-button :href="route('vendor.products.create')" size="sm">{{ __('ui.catalog.product_create') }}</x-button>
    </div>
    <form method="GET" class="mb-4 max-w-md">
        <x-input name="q" :label="__('ui.nav.search')" :value="$term" />
    </form>
    <div class="overflow-x-auto rounded-2xl border border-twende-line dark:border-white/10">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-twende-light text-twende-muted dark:bg-white/5">
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('ui.catalog.name') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('ui.catalog.sku') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('ui.catalog.status') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('ui.catalog.stock') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr class="border-t border-twende-line dark:border-white/10">
                        <td class="px-4 py-3 font-medium">{{ $product->name }}</td>
                        <td class="px-4 py-3">{{ $product->sku }}</td>
                        <td class="px-4 py-3">{{ __('ui.catalog.product_statuses.'.$product->status->value) }}</td>
                        <td class="px-4 py-3">{{ $product->availableQuantity() }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('vendor.products.edit', $product) }}" class="font-semibold text-twende-green">{{ __('ui.catalog.edit') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-twende-muted">{{ __('ui.catalog.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">
        <x-pagination :paginator="$products" />
    </div>
</x-layouts.dashboard>
