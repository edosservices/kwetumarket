<x-layouts.dashboard :title="__('ui.catalog.shops_title')">
    @if (session('status'))
        <x-alert variant="success" class="mb-4">{{ session('status') }}</x-alert>
    @endif
    <h1 class="text-2xl font-bold">{{ __('ui.catalog.shops_title') }}</h1>
    <div class="mt-6 overflow-x-auto rounded-2xl border border-twende-line dark:border-white/10">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-twende-light text-twende-muted dark:bg-white/5">
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('ui.catalog.name') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('ui.catalog.status') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('ui.catalog.location') }}</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($shops as $shop)
                    <tr class="border-t border-twende-line dark:border-white/10">
                        <td class="px-4 py-3 font-medium">{{ $shop->name }}</td>
                        <td class="px-4 py-3">{{ __('ui.catalog.shop_statuses.'.$shop->status->value) }}</td>
                        <td class="px-4 py-3">{{ $shop->location }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.shops.edit', $shop) }}" class="font-semibold text-twende-green">{{ __('ui.catalog.edit') }}</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-6"><x-pagination :paginator="$shops" /></div>
</x-layouts.dashboard>
