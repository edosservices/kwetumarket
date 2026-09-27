<x-layouts.delivery :title="__('ui.modules.missions')">
    <h1 class="text-2xl font-bold">{{ __('ui.modules.missions') }}</h1>
    <div class="mt-6 grid gap-3">
        @forelse ($deliveries as $delivery)
            <a href="{{ route('delivery.missions.show', $delivery) }}" class="rounded-2xl border border-twende-line px-4 py-3 dark:border-white/10">
                <span class="font-semibold">{{ $delivery->order?->number }}</span>
                <span class="mt-1 block text-sm text-twende-muted">{{ $delivery->status }}</span>
            </a>
        @empty
            <x-empty-state :title="__('ui.modules.empty')" />
        @endforelse
    </div>
    <div class="mt-6">
        <x-pagination :paginator="$deliveries" />
    </div>
</x-layouts.delivery>
