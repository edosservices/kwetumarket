<x-layouts.storefront :title="__('ui.search.title')">
    <section class="mx-auto max-w-3xl px-4 py-10">
        <h1 class="text-3xl font-bold">{{ __('ui.search.title') }}</h1>
        <div class="mt-6">
            <livewire:marketplace-search variant="hero" />
        </div>
        <p class="mt-4 text-sm text-twende-muted">{{ trans_choice('ui.search.results', $results['total'], ['count' => $results['total']]) }}</p>
        <div class="mt-6">
            @if ($query !== '')
                <x-empty-state :title="__('ui.search.empty_query', ['query' => $query])" />
            @else
                <x-empty-state :title="__('ui.search.empty')" />
            @endif
        </div>
    </section>
</x-layouts.storefront>
