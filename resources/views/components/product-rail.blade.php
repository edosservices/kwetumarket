@props([
    'title',
    'products',
    'empty' => null,
    'href' => null,
    'kicker' => null,
])

<section {{ $attributes->class('mx-auto max-w-[100rem] px-3 py-3 sm:px-4') }}>
    <div class="flex items-end justify-between gap-3">
        <div class="min-w-0">
            <h2 class="text-lg font-bold">{{ $title }}</h2>
            @if ($kicker)
                <p class="text-xs font-semibold text-twende-muted">{{ $kicker }}</p>
            @endif
        </div>
        @if ($href)
            <a href="{{ $href }}" class="shrink-0 text-sm font-semibold text-twende-green">{{ __('ui.catalog.see_all') }}</a>
        @endif
    </div>
    <div class="mt-2">
        @if ($products->isEmpty())
            <x-empty-state :title="__('ui.store.empty_products')" :description="$empty">
                <x-slot:action>
                    <x-button :href="route('categories.index')" variant="outline" size="sm">{{ __('ui.store.browse_categories') }}</x-button>
                </x-slot:action>
            </x-empty-state>
        @else
            <div class="grid min-w-0 max-w-full grid-cols-2 gap-2 min-[480px]:grid-cols-3 md:flex md:gap-3 md:overflow-x-auto md:pb-1">
                @foreach ($products as $product)
                    <div class="min-w-0 md:w-44 md:shrink-0">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
