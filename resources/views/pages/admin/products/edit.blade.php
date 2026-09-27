<x-layouts.admin :title="$product->name">
    <h1 class="text-2xl font-bold">{{ $product->name }}</h1>
    <p class="mt-2 text-sm text-twende-muted">{{ $product->status }}</p>
    <form method="POST" action="{{ route('admin.products.update', $product) }}" class="mt-6 max-w-xl space-y-4">
        @csrf
        @method('PUT')
        <x-input name="name" :label="__('ui.fields.name')" :value="$product->name" required />
        <x-input name="price_minor" type="number" min="0" :label="__('ui.fields.price_minor')" :value="$product->price_minor" required />
        <x-input name="stock" type="number" min="0" :label="__('ui.fields.stock')" :value="$product->stock" required />
        <x-button type="submit">{{ __('ui.actions.save') }}</x-button>
    </form>
</x-layouts.admin>
