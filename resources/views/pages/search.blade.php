<x-layouts.storefront :title="__('ui.search.title')">
    <section class="mx-auto max-w-7xl px-4 py-10">
        <h1 class="text-3xl font-bold">{{ __('ui.search.title') }}</h1>
        <div class="mt-6">
            <livewire:marketplace-search variant="hero" />
        </div>
        <div class="mt-6">
            <x-catalog-filters :action="route('search')" :filters="$filters" :categories="$categories" :brands="$brands" :shops="$shops" :query="$query" :show-query="false" />
        </div>
        <p class="mt-4 text-sm text-twende-muted">{{ trans_choice('ui.search.results', $results['total'], ['count' => $results['total']]) }}</p>
        <div class="mt-6">
            @if (($results['total'] ?? 0) === 0)
                @if ($query !== '')
                    <x-empty-state :title="__('ui.search.empty_query', ['query' => $query])" />
                @else
                    <x-empty-state :title="__('ui.search.empty')" />
                @endif
            @else
                <div class="grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4 xl:grid-cols-4">
                    @foreach ($results['items'] as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
                @if ($results['paginator'])
                    <div class="mt-8">
                        <x-pagination :paginator="$results['paginator']" />
                    </div>
                @endif
            @endif
        </div>
    </section>
</x-layouts.storefront>
