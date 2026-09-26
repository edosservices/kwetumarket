<x-layouts.dashboard :title="__('ui.catalog.brands')">
    @if (session('status'))
        <x-alert variant="success" class="mb-4">{{ session('status') }}</x-alert>
    @endif
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">{{ __('ui.catalog.brands') }}</h1>
        <x-button :href="route('admin.brands.create')" size="sm">{{ __('ui.catalog.create') }}</x-button>
    </div>
    <div class="overflow-x-auto rounded-2xl border border-twende-line dark:border-white/10">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-twende-light text-twende-muted dark:bg-white/5">
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('ui.catalog.name') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('ui.catalog.status') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('ui.catalog.products_title') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($brands as $brand)
                    <tr class="border-t border-twende-line dark:border-white/10">
                        <td class="px-4 py-3 font-medium">{{ $brand->name }}</td>
                        <td class="px-4 py-3">{{ __('ui.catalog.catalog_statuses.'.$brand->status->value) }}</td>
                        <td class="px-4 py-3">{{ $brand->products_count }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.brands.edit', $brand) }}" class="font-semibold text-twende-green">{{ __('ui.catalog.edit') }}</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-6"><x-pagination :paginator="$brands" /></div>
</x-layouts.dashboard>
