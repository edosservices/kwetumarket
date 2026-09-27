@props([
    'title',
    'query' => '',
    'results',
    'filters' => [],
    'categories' => collect(),
    'brands' => collect(),
    'shops' => collect(),
    'cities' => collect(),
    'action',
    'showQuery' => false,
    'hideShop' => false,
])

@php
    $sorts = [
        'relevance' => __('ui.catalog.sort_relevance'),
        'price_asc' => __('ui.catalog.sort_price_asc'),
        'price_desc' => __('ui.catalog.sort_price_desc'),
        'newest' => __('ui.catalog.sort_newest'),
        'bestsellers' => __('ui.catalog.sort_bestsellers'),
        'rating' => __('ui.catalog.sort_rating'),
    ];
@endphp

<section class="mx-auto max-w-[100rem] px-3 py-4 sm:px-4" x-data="{ filtersOpen: false, sortOpen: false }">
    <form method="GET" action="{{ $action }}" class="lg:grid lg:grid-cols-[15.5rem_minmax(0,1fr)] lg:items-start lg:gap-4">
        <div class="mb-3 flex gap-2 lg:hidden">
            <button type="button" class="h-10 flex-1 rounded-lg border border-twende-line bg-white text-xs font-bold uppercase tracking-wide dark:border-white/15 dark:bg-twende-night-card" x-on:click="filtersOpen = ! filtersOpen; sortOpen = false">{{ __('ui.store.filters') }}</button>
            <button type="button" class="h-10 flex-1 rounded-lg border border-twende-line bg-white text-xs font-bold uppercase tracking-wide dark:border-white/15 dark:bg-twende-night-card" x-on:click="sortOpen = ! sortOpen; filtersOpen = false">{{ __('ui.catalog.sort') }}</button>
        </div>
        <aside class="mb-4 rounded-lg border border-twende-line bg-white p-3 dark:border-white/10 dark:bg-twende-night-card lg:sticky lg:top-28 lg:mb-0" x-bind:class="filtersOpen ? 'block' : 'hidden lg:block'">
            <x-catalog-filters :filters="$filters" :categories="$categories" :brands="$brands" :shops="$shops" :cities="$cities" :query="$query" :show-query="$showQuery" :hide-shop="$hideShop" />
        </aside>
        <div class="min-w-0">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold sm:text-2xl">
                        @if ($query !== '')
                            {{ __('ui.store.results_for', ['query' => $query]) }}
                        @else
                            {{ $title }}
                        @endif
                    </h1>
                    <p class="mt-1 text-sm text-twende-muted">{{ trans_choice('ui.search.results', $results['total'] ?? 0, ['count' => $results['total'] ?? 0]) }}</p>
                </div>
                <label class="gap-1 text-xs font-semibold" x-bind:class="sortOpen ? 'grid' : 'hidden lg:grid'">
                    {{ __('ui.store.sort_by') }}
                    <select name="sort" class="h-10 rounded-lg border border-twende-line bg-white px-2 text-sm font-medium dark:border-white/15 dark:bg-twende-night" onchange="this.form.requestSubmit()">
                        @foreach ($sorts as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'relevance') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <div class="mt-4">
                @if (($results['total'] ?? 0) === 0)
                    <x-empty-state :title="$query !== '' ? __('ui.search.empty_query', ['query' => $query]) : __('ui.store.empty_products')" :description="__('ui.catalog.empty')">
                        <x-slot:action>
                            <x-button :href="route('categories.index')" variant="outline" size="sm">{{ __('ui.store.browse_categories') }}</x-button>
                        </x-slot:action>
                    </x-empty-state>
                @else
                    <div class="grid grid-cols-2 gap-2 sm:gap-3 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                        @foreach ($results['items'] as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>
                    @if ($results['paginator'])
                        <div class="mt-6">
                            <x-pagination :paginator="$results['paginator']" />
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </form>
</section>
