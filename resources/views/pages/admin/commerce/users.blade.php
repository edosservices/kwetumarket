<x-layouts.dashboard :title="__('commerce.users')">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <h1 class="text-xl font-bold">{{ $role === 'client' ? __('ui.dashboard.clients') : __('commerce.users') }}</h1>
        <form method="GET" class="flex gap-2">
            <label class="sr-only" for="user-role">{{ __('ui.catalog.status') }}</label>
            <select id="user-role" name="role" class="h-9 rounded-md border border-twende-line bg-white px-2 text-sm dark:border-white/15 dark:bg-twende-night" onchange="this.form.requestSubmit()">
                <option value="">{{ __('ui.catalog.all') }}</option>
                @foreach (['client' => __('ui.dashboard.clients'), 'vendor' => __('ui.catalog.vendors'), 'delivery_agent' => __('ui.nav.delivery'), 'admin' => __('ui.nav.admin')] as $value => $label)
                    <option value="{{ $value }}" @selected($role === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>
    @if ($users->isEmpty())
        <div class="mt-4"><x-empty-state :title="__('ui.catalog.none')" /></div>
    @else
        <ul class="mt-4 divide-y divide-twende-line overflow-hidden rounded-lg border border-twende-line bg-white dark:divide-white/10 dark:border-white/10 dark:bg-twende-night-card">
            @foreach ($users as $person)
                <li class="flex flex-wrap items-center justify-between gap-2 px-3 py-2 text-sm">
                    <span class="min-w-0">{{ $person->name }} · {{ $person->email }}</span>
                    <x-badge>{{ $person->getRoleNames()->join(', ') }}</x-badge>
                </li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $users->links() }}</div>
    @endif
</x-layouts.dashboard>
