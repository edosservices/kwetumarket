<x-layouts.storefront :title="__('ui.catalog.products_title')" :description="__('ui.catalog.products_intro')">
    <x-catalog-browser
        :title="__('ui.catalog.products_title')"
        :query="$query"
        :results="$results"
        :filters="$filters"
        :categories="$categories"
        :brands="$brands"
        :shops="$shops"
        :cities="$cities ?? collect()"
        :action="route('products.index')"
        :show-query="true"
    />
</x-layouts.storefront>
