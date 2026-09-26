<x-layouts.dashboard :title="__('ui.catalog.products_title')">
    @if (session('status'))
        <x-alert variant="success" class="mb-4">{{ session('status') }}</x-alert>
    @endif
    <h1 class="text-2xl font-bold">{{ __('ui.catalog.products_title') }}</h1>
    <form method="GET" class="mt-4 grid max-w-3xl gap-3 sm:grid-cols-2">
        <x-input name="q" :label="__('ui.nav.search')" :value="$term" />
        <x-select name="status" :label="__('ui.catalog.status')" :selected="$status" :options="['' => __('ui.catalog.all'), 'draft' => __('ui.catalog.product_statuses.draft'), 'pending' => __('ui.catalog.product_statuses.pending'), 'published' => __('ui.catalog.product_statuses.published'), 'rejected' => __('ui.catalog.product_statuses.rejected'), 'archived' => __('ui.catalog.product_statuses.archived')]" />
        <x-button type="submit" size="sm">{{ __('ui.catalog.apply') }}</x-button>
    </form>
    <div class="mt-6 overflow-x-auto rounded-2xl border border-twende-line dark:border-white/10">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-twende-light text-twende-muted dark:bg-white/5">
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('ui.catalog.name') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('ui.catalog.shop') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('ui.catalog.status') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr class="border-t border-twende-line dark:border-white/10">
                        <td class="px-4 py-3 font-medium">{{ $product->name }}</td>
                        <td class="px-4 py-3">{{ $product->shop->name }}</td>
                        <td class="px-4 py-3">{{ __('ui.catalog.product_statuses.'.$product->status->value) }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.products.edit', $product) }}" class="font-semibold text-twende-green">{{ __('ui.catalog.edit') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-twende-muted">{{ __('ui.catalog.empty') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6"><x-pagination :paginator="$products" /></div>
</x-layouts.dashboard>
