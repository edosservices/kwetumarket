<x-layouts.storefront :title="__('ui.catalog.shops_title')" :description="__('ui.catalog.shops_body')">
    <section class="mx-auto max-w-7xl px-4 py-8">
        <h1 class="text-3xl font-bold">{{ __('ui.catalog.shops_title') }}</h1>
        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-twende-muted">{{ __('ui.catalog.shops_intro') }}</p>
        @if ($shops->isEmpty())
            <div class="mt-6">
                <x-empty-state :title="__('ui.catalog.shops_body')" />
            </div>
        @else
            <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($shops as $shop)
                    <x-supplier-card :shop="$shop" />
                @endforeach
            </div>
            <div class="mt-8">
                <x-pagination :paginator="$shops" />
            </div>
        @endif
    </section>
</x-layouts.storefront>
