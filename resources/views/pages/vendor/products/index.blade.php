<x-layouts.vendor :title="__('ui.modules.products')">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">{{ __('ui.modules.products') }}</h1>
    </div>
    @can('create', \App\Models\Product::class)
        <form method="POST" action="{{ route('vendor.products.store') }}" class="mt-6 grid gap-4 rounded-2xl border border-twende-line p-4 sm:grid-cols-4 dark:border-white/10">
            @csrf
            <x-input name="name" :label="__('ui.fields.name')" required />
            <x-input name="price_minor" type="number" min="0" :label="__('ui.fields.price_minor')" required />
            <x-input name="stock" type="number" min="0" :label="__('ui.fields.stock')" required />
            <div class="flex items-end">
                <x-button type="submit">{{ __('ui.actions.create') }}</x-button>
            </div>
        </form>
    @endcan
    <div class="mt-6 grid gap-3">
        @forelse ($products as $product)
            <a href="{{ route('vendor.products.show', $product) }}" class="rounded-2xl border border-twende-line px-4 py-3 hover:border-twende-red dark:border-white/10">
                <span class="font-semibold">{{ $product->name }}</span>
                <span class="mt-1 block text-sm text-twende-muted">{{ $product->status }} · {{ $product->stock }}</span>
            </a>
        @empty
            <x-empty-state :title="__('ui.modules.empty')" />
        @endforelse
    </div>
    <div class="mt-6">
        <x-pagination :paginator="$products" />
    </div>
</x-layouts.vendor>
