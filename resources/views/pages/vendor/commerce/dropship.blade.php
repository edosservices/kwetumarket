<x-layouts.dashboard :title="__('experience.dropship')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('experience.dropship') }}</h1>
    <p class="mt-2 max-w-xl text-sm text-twende-muted">{{ __('commerce.dropship_help') }}</p>
    <div class="mt-6 grid gap-4">
        @forelse ($products as $product)
            <form method="POST" action="{{ route('vendor.dropship.update', $product) }}" class="grid gap-3 rounded-2xl border border-twende-line p-4 dark:border-white/10 md:grid-cols-2">
                @csrf
                @method('PUT')
                <h2 class="font-semibold md:col-span-2">{{ $product->name }}</h2>
                <x-input name="supplier_name" :label="__('commerce.supplier')" :value="old('supplier_name', $product->supplier_name)" />
                <x-input name="supplier_sku" :label="__('ui.catalog.sku')" :value="old('supplier_sku', $product->supplier_sku)" />
                <x-input name="supplier_price" :label="__('experience.supplier_price')" :value="old('supplier_price', $product->supplier_price ? \App\Support\Money::toInput((int) $product->supplier_price) : '')" inputmode="decimal" />
                <x-input name="price" :label="__('experience.reseller_price')" :value="old('price', \App\Support\Money::toInput((int) $product->price))" inputmode="decimal" />
                @if ($product->supplier_price)
                    <p class="text-sm md:col-span-2">{{ __('experience.margin') }} : {{ \App\Support\Money::format((int) $product->price - (int) $product->supplier_price, $product->currency) }}</p>
                @endif
                <button class="h-10 w-fit rounded-full bg-twende-green px-4 text-sm font-semibold text-white">{{ __('ui.catalog.save') }}</button>
            </form>
        @empty
            <x-empty-state :title="__('ui.home.popular_empty')" />
        @endforelse
    </div>
</x-layouts.dashboard>
