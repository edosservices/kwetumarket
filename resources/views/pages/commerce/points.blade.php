<x-layouts.dashboard :title="__('operations.points')">
    <h1 class="text-2xl font-bold">{{ __('operations.points') }}</h1>
    <dl class="mt-6 grid gap-3 sm:grid-cols-4">
        <div class="rounded-2xl border border-twende-line p-4 dark:border-white/10"><dt class="text-sm text-twende-muted">{{ __('operations.balance') }}</dt><dd class="text-xl font-bold">{{ $summary['balance'] }}</dd></div>
        <div class="rounded-2xl border border-twende-line p-4 dark:border-white/10"><dt class="text-sm text-twende-muted">{{ __('operations.earned') }}</dt><dd class="text-xl font-bold">{{ $summary['earned'] }}</dd></div>
        <div class="rounded-2xl border border-twende-line p-4 dark:border-white/10"><dt class="text-sm text-twende-muted">{{ __('operations.spent') }}</dt><dd class="text-xl font-bold">{{ $summary['spent'] }}</dd></div>
        <div class="rounded-2xl border border-twende-line p-4 dark:border-white/10"><dt class="text-sm text-twende-muted">{{ __('operations.expired') }}</dt><dd class="text-xl font-bold">{{ $summary['expired'] }}</dd></div>
    </dl>
    <ul class="mt-6 space-y-2 text-sm">
        @forelse ($entries as $entry)
            <li class="rounded-xl bg-twende-light px-3 py-2 dark:bg-white/5">{{ $entry->created_at->timezone(config('app.timezone'))->format('d/m/Y') }} · {{ $entry->type }} · {{ $entry->points }}</li>
        @empty
            <li class="text-twende-muted">{{ __('operations.points') }}</li>
        @endforelse
    </ul>
</x-layouts.dashboard>
