<x-layouts.storefront :title="__('ui.catalog.categories_title')" :description="__('ui.catalog.categories_body')">
    <section class="mx-auto max-w-7xl px-4 py-8">
        <h1 class="text-3xl font-bold">{{ __('ui.catalog.categories_title') }}</h1>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-twende-muted">{{ __('ui.catalog.categories_intro') }}</p>
        @if ($categories->isEmpty())
            <div class="mt-6">
                <x-empty-state :title="__('ui.catalog.categories_body')" />
            </div>
        @else
            <div class="mt-8 space-y-8">
                @foreach ($categories as $category)
                    <section>
                        <h2 class="text-xl font-semibold">
                            <a href="{{ route('categories.show', $category) }}" class="hover:text-twende-red">{{ $category->name }}</a>
                        </h2>
                        @if ($category->description)
                            <p class="mt-1 text-sm text-twende-muted">{{ $category->description }}</p>
                        @endif
                        @if ($category->children->isNotEmpty())
                            <div class="mt-4 flex gap-3 overflow-x-auto pb-2">
                                @foreach ($category->children as $child)
                                    <x-category-card :name="$child->name" :href="route('categories.show', $child)" />
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.storefront>
