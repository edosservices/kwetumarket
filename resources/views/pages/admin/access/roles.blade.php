<x-layouts.admin :title="$title">
    <h1 class="text-2xl font-bold">{{ $title }}</h1>
    <p class="mt-2 max-w-3xl text-sm text-twende-muted">{{ __('ui.access.roles_intro') }}</p>
    <div class="mt-6 space-y-4">
        @foreach ($roles as $role)
            <article class="rounded-2xl border border-twende-line p-4 dark:border-white/10">
                <h2 class="text-lg font-semibold">{{ $role->name }}</h2>
                <p class="mt-1 text-sm text-twende-muted">{{ \App\Support\Rbac\RoleCatalog::description($role->name) }}</p>
                <p class="mt-3 text-sm font-semibold">{{ $role->permissions->count() }} {{ __('ui.fields.permissions') }}</p>
                <ul class="mt-2 flex flex-wrap gap-2">
                    @foreach ($role->permissions->sortBy('name') as $permission)
                        <li class="rounded-full bg-twende-light px-3 py-1 text-xs dark:bg-white/10">{{ $permission->name }}</li>
                    @endforeach
                </ul>
            </article>
        @endforeach
    </div>
    @if ($holders->isNotEmpty())
        <h2 class="mt-10 text-xl font-bold">{{ __('ui.access.holders') }}</h2>
        <div class="mt-4 overflow-x-auto rounded-2xl border border-twende-line dark:border-white/10">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-twende-light text-twende-muted dark:bg-white/5">
                    <tr>
                        <th class="px-4 py-3 font-semibold">{{ __('ui.fields.user') }}</th>
                        <th class="px-4 py-3 font-semibold">{{ __('ui.fields.role') }}</th>
                        <th class="px-4 py-3 font-semibold">{{ __('ui.fields.permissions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($holders as $holder)
                        <tr class="border-t border-twende-line dark:border-white/10">
                            <td class="px-4 py-3">{{ $holder->name }}</td>
                            <td class="px-4 py-3">{{ $holder->getRoleNames()->join(', ') }}</td>
                            <td class="px-4 py-3">{{ $holder->getAllPermissions()->pluck('name')->sort()->join(', ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.admin>
