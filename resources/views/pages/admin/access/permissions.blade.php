<x-layouts.admin :title="$title">
    <h1 class="text-2xl font-bold">{{ $title }}</h1>
    <p class="mt-2 max-w-3xl text-sm text-twende-muted">{{ __('ui.access.permissions_intro') }}</p>
    <div class="mt-6 overflow-x-auto rounded-2xl border border-twende-line dark:border-white/10">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-twende-light text-twende-muted dark:bg-white/5">
                <tr>
                    <th class="px-4 py-3 font-semibold">{{ __('ui.fields.permission') }}</th>
                    <th class="px-4 py-3 font-semibold">{{ __('ui.fields.module') }}</th>
                    <th class="px-4 py-3 font-semibold">{{ __('ui.fields.role') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($permissions as $permission)
                    <tr class="border-t border-twende-line dark:border-white/10">
                        <td class="px-4 py-3">{{ $permission->name }}</td>
                        <td class="px-4 py-3">{{ strstr($permission->name, '.', true) ?: $permission->name }}</td>
                        <td class="px-4 py-3">{{ $permission->roles->pluck('name')->sort()->join(', ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($holders->isNotEmpty())
        <h2 class="mt-10 text-xl font-bold">{{ __('ui.access.holders') }}</h2>
        <div class="mt-4 space-y-3">
            @foreach ($holders as $holder)
                <article class="rounded-2xl border border-twende-line p-4 dark:border-white/10">
                    <h3 class="font-semibold">{{ $holder->name }}</h3>
                    <p class="mt-1 text-sm text-twende-muted">{{ $holder->getRoleNames()->join(', ') }}</p>
                    <p class="mt-2 text-sm">{{ $holder->getAllPermissions()->pluck('name')->sort()->join(', ') }}</p>
                </article>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
