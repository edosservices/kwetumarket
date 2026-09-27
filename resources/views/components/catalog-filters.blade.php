@props([
    'filters' => [],
    'categories' => collect(),
    'brands' => collect(),
    'shops' => collect(),
    'cities' => collect(),
    'query' => '',
    'showQuery' => true,
    'hideShop' => false,
])

<div class="grid gap-3">
    @if ($showQuery)
        <x-input name="q" :label="__('ui.nav.search')" :value="$query" />
    @else
        <input type="hidden" name="q" value="{{ $query }}">
    @endif
    <x-select name="category" :label="__('ui.catalog.category')" :selected="$filters['category'] ?? ''" :options="['' => __('ui.catalog.all')] + $categories->pluck('name', 'id')->all()" />
    <div class="grid grid-cols-2 gap-2">
        <x-input name="price_min" :label="__('ui.catalog.price_min')" :value="request('price_min')" inputmode="decimal" />
        <x-input name="price_max" :label="__('ui.catalog.price_max')" :value="request('price_max')" inputmode="decimal" />
    </div>
    <x-select name="brand" :label="__('ui.catalog.brands')" :selected="$filters['brand'] ?? ''" :options="['' => __('ui.catalog.all')] + $brands->pluck('name', 'id')->all()" />
    <x-select name="min_rating" :label="__('ui.store.rating_filter')" :selected="$filters['min_rating'] ?? ''" :options="['' => __('ui.catalog.all'), 4 => __('ui.store.stars_up', ['count' => 4]), 3 => __('ui.store.stars_up', ['count' => 3]), 2 => __('ui.store.stars_up', ['count' => 2]), 1 => __('ui.store.stars_up', ['count' => 1])]" />
    <x-select name="availability" :label="__('ui.catalog.availability')" :selected="$filters['availability'] ?? ''" :options="['' => __('ui.catalog.all'), 'in_stock' => __('ui.catalog.in_stock'), 'out_of_stock' => __('ui.catalog.out_of_stock')]" />
    @if ($hideShop)
        <input type="hidden" name="shop" value="{{ $filters['shop'] ?? '' }}">
    @else
        <x-select name="shop" :label="__('ui.store.vendors')" :selected="$filters['shop'] ?? ''" :options="['' => __('ui.catalog.all')] + $shops->pluck('name', 'id')->all()" />
    @endif
    @if ($cities->isNotEmpty())
        <x-select name="city" :label="__('ui.catalog.location')" :selected="$filters['city'] ?? ''" :options="['' => __('ui.catalog.all')] + $cities->mapWithKeys(fn ($city) => [$city => $city])->all()" />
    @endif
    <x-select name="condition" :label="__('ui.catalog.condition')" :selected="$filters['condition'] ?? ''" :options="['' => __('ui.catalog.all'), 'new' => __('ui.catalog.conditions.new'), 'used' => __('ui.catalog.conditions.used'), 'refurbished' => __('ui.catalog.conditions.refurbished')]" />
    <div class="flex flex-wrap gap-2">
        <x-button type="submit" size="sm">{{ __('ui.catalog.apply') }}</x-button>
        <x-button :href="url()->current()" variant="outline" size="sm">{{ __('ui.catalog.reset') }}</x-button>
    </div>
</div>
