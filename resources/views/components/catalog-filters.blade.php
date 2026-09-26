@props([
    'action',
    'filters' => [],
    'categories' => collect(),
    'brands' => collect(),
    'shops' => collect(),
    'query' => '',
    'showQuery' => true,
])

<form method="GET" action="{{ $action }}" class="grid gap-3 rounded-2xl border border-twende-line bg-white p-4 dark:border-white/10 dark:bg-twende-night-card sm:grid-cols-2 lg:grid-cols-4">
    @if ($showQuery)
        <x-input name="q" :label="__('ui.nav.search')" :value="$query" />
    @endif
    <x-select name="category" :label="__('ui.catalog.category')" :selected="$filters['category'] ?? ''" :options="['' => __('ui.catalog.all')] + $categories->pluck('name', 'id')->all()" />
    <x-select name="brand" :label="__('ui.catalog.brand')" :selected="$filters['brand'] ?? ''" :options="['' => __('ui.catalog.all')] + $brands->pluck('name', 'id')->all()" />
    <x-select name="shop" :label="__('ui.catalog.shop')" :selected="$filters['shop'] ?? ''" :options="['' => __('ui.catalog.all')] + $shops->pluck('name', 'id')->all()" />
    <x-input name="price_min" :label="__('ui.catalog.price_min')" :value="request('price_min')" inputmode="decimal" />
    <x-input name="price_max" :label="__('ui.catalog.price_max')" :value="request('price_max')" inputmode="decimal" />
    <x-select name="availability" :label="__('ui.catalog.availability')" :selected="$filters['availability'] ?? ''" :options="['' => __('ui.catalog.all'), 'in_stock' => __('ui.catalog.in_stock'), 'out_of_stock' => __('ui.catalog.out_of_stock')]" />
    <x-select name="condition" :label="__('ui.catalog.condition')" :selected="$filters['condition'] ?? ''" :options="['' => __('ui.catalog.all'), 'new' => __('ui.catalog.conditions.new'), 'used' => __('ui.catalog.conditions.used'), 'refurbished' => __('ui.catalog.conditions.refurbished')]" />
    <x-select name="sort" :label="__('ui.catalog.sort')" :selected="$filters['sort'] ?? 'relevance'" :options="['relevance' => __('ui.catalog.sort_relevance'), 'price_asc' => __('ui.catalog.sort_price_asc'), 'price_desc' => __('ui.catalog.sort_price_desc'), 'newest' => __('ui.catalog.sort_newest')]" />
    <div class="flex items-end gap-2">
        <x-button type="submit">{{ __('ui.catalog.apply') }}</x-button>
        <x-button :href="$action" variant="outline">{{ __('ui.catalog.reset') }}</x-button>
    </div>
</form>
