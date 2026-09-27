<x-layouts.storefront :title="__('ui.search.title')">
    <x-catalog-browser
        :title="__('ui.search.title')"
        :query="$query"
        :results="$results"
        :filters="$filters"
        :categories="$categories"
        :brands="$brands"
        :shops="$shops"
        :cities="$cities ?? collect()"
        :action="route('search')"
    />
</x-layouts.storefront>
