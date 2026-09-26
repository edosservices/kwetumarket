<x-layouts.storefront :title="$category->name" :description="$category->description">
    <section class="mx-auto max-w-7xl px-4 py-8">
        <p class="text-sm text-twende-muted">
            <a href="{{ route('categories.index') }}" class="font-semibold text-twende-green">{{ __('ui.catalog.categories_title') }}</a>
            @if ($category->parent)
                <span> / </span>
                <a href="{{ route('categories.show', $category->parent) }}">{{ $category->parent->name }}</a>
            @endif
        </p>
        <h1 class="mt-2 text-3xl font-bold">{{ $category->name }}</h1>
        @if ($category->description)
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-twende-muted">{{ $category->description }}</p>
        @endif
        @if ($category->children->isNotEmpty())
            <div class="mt-6 flex gap-3 overflow-x-auto pb-2">
                @foreach ($category->children as $child)
                    <x-category-card :name="$child->name" :href="route('categories.show', $child)" />
                @endforeach
            </div>
        @endif
        <p class="mt-6 text-sm text-twende-muted">{{ trans_choice('ui.search.results', $results['total'], ['count' => $results['total']]) }}</p>
        <div class="mt-6">
            @if ($results['total'] === 0)
                <x-empty-state :title="__('ui.catalog.empty')" />
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
