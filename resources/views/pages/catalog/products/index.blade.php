<x-layouts.storefront :title="__('ui.catalog.products_title')" :description="__('ui.catalog.products_intro')">
    <section class="mx-auto max-w-7xl px-4 py-8">
        <h1 class="text-3xl font-bold">{{ __('ui.catalog.products_title') }}</h1>
        <p class="mt-2 text-sm text-twende-muted">{{ trans_choice('ui.search.results', $results['total'], ['count' => $results['total']]) }}</p>
        <div class="mt-6">
            <x-catalog-filters :action="route('products.index')" :filters="$filters" :categories="$categories" :brands="$brands" :shops="$shops" :query="$query" />
        </div>
        <div class="mt-6">
            @if ($results['total'] === 0)
                <x-empty-state :title="$query !== '' ? __('ui.search.empty_query', ['query' => $query]) : __('ui.catalog.empty')" />
            @else
                <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4 xl:grid-cols-4">
                    @foreach ($results['items'] as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
                <div class="mt-8">
                    <x-pagination :paginator="$results['paginator']" />
                </div>
            @endif
        </div>
    </section>
</x-layouts.storefront>
