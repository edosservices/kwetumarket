<x-layouts.dashboard :title="__('ui.catalog.vendors')">
    @if (session('status'))
        <x-alert variant="success" class="mb-4">{{ session('status') }}</x-alert>
    @endif
    <h1 class="text-2xl font-bold">{{ __('ui.catalog.vendors') }}</h1>
    <div class="mt-6 space-y-4">
        @foreach ($vendors as $vendor)
            <article class="rounded-2xl border border-twende-line p-4 dark:border-white/10">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="font-semibold">{{ $vendor->user->name }}</h2>
                        <p class="text-sm text-twende-muted">{{ $vendor->user->email }}</p>
                        <p class="mt-1 text-sm">{{ $vendor->shops->pluck('name')->join(', ') }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.vendors.update', $vendor) }}" class="flex items-end gap-2">
                        @csrf
                        @method('PUT')
                        <x-select name="status" :label="__('ui.catalog.status')" :selected="$vendor->status->value" :options="[
                            'pending' => __('ui.catalog.vendor_statuses.pending'),
                            'active' => __('ui.catalog.vendor_statuses.active'),
                            'suspended' => __('ui.catalog.vendor_statuses.suspended'),
                            'blocked' => __('ui.catalog.vendor_statuses.blocked'),
                        ]" />
                        <x-button type="submit" size="sm">{{ __('ui.catalog.save') }}</x-button>
                    </form>
                </div>
            </article>
        @endforeach
    </div>
    <div class="mt-6"><x-pagination :paginator="$vendors" /></div>
    @if ($missing->isNotEmpty())
        <h2 class="mt-10 text-lg font-semibold">{{ __('ui.catalog.vendors_missing') }}</h2>
        <div class="mt-4 space-y-3">
            @foreach ($missing as $user)
                <form method="POST" action="{{ route('admin.vendors.store') }}" class="flex flex-wrap items-end gap-2 rounded-2xl border border-twende-line p-4 dark:border-white/10">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <div class="min-w-48 flex-1">
                        <p class="font-medium">{{ $user->name }}</p>
                        <p class="text-sm text-twende-muted">{{ $user->email }}</p>
                    </div>
                    <x-select name="status" :label="__('ui.catalog.status')" selected="pending" :options="[
                        'pending' => __('ui.catalog.vendor_statuses.pending'),
                        'active' => __('ui.catalog.vendor_statuses.active'),
                    ]" />
                    <x-button type="submit" size="sm">{{ __('ui.catalog.create') }}</x-button>
                </form>
            @endforeach
        </div>
    @endif
</x-layouts.dashboard>
