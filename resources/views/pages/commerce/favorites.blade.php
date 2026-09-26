<x-layouts.dashboard :title="__('commerce.favorites')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('commerce.favorites') }}</h1>
    @if ($products->isEmpty())
        <div class="mt-6"><x-empty-state :title="__('commerce.favorites_empty_title')" :description="__('commerce.favorites_empty_body')"><x-slot:action><x-button :href="route('products.index')">{{ __('commerce.browse') }}</x-button></x-slot:action></x-empty-state></div>
    @else
        <div class="mt-6 grid grid-cols-2 gap-3 md:grid-cols-3">
            @foreach ($products as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    @endif
</x-layouts.dashboard>
