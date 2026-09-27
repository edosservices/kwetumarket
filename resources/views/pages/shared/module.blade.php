<x-dynamic-component :component="'layouts.'.$area" :title="$title">
    <h1 class="text-2xl font-bold">{{ $title }}</h1>
    @if ($rows === [])
        <div class="mt-6">
            <x-empty-state :title="__('ui.modules.empty')" />
        </div>
    @else
        <div class="mt-6 overflow-x-auto rounded-2xl border border-twende-line dark:border-white/10">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-twende-light text-twende-muted dark:bg-white/5">
                    <tr>
                        @foreach ($columns as $column)
                            <th class="px-4 py-3 font-semibold">{{ $column }}</th>
                        @endforeach
                        @if ($area === 'admin' && $module === 'utilisateurs')
                            <th class="px-4 py-3 font-semibold">{{ __('ui.fields.action') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr class="border-t border-twende-line dark:border-white/10">
                            @foreach ($row['cells'] as $cell)
                                <td class="px-4 py-3">{{ $cell }}</td>
                            @endforeach
                            @if ($area === 'admin' && $module === 'utilisateurs')
                                <td class="px-4 py-3">
                                    @can('suspend', \App\Models\User::query()->find($row['suspend_user_id']))
                                        <form method="POST" action="{{ route('admin.users.suspend', $row['suspend_user_id']) }}">
                                            @csrf
                                            <x-button type="submit" size="sm" variant="outline">{{ __('ui.actions.suspend') }}</x-button>
                                        </form>
                                    @endcan
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($area === 'admin' && $module === 'parametres')
        @can('settings.edit')
            <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-8 max-w-xl space-y-4">
                @csrf
                <x-input name="key" :label="__('ui.fields.key')" required />
                <x-input name="value" :label="__('ui.fields.value')" />
                <x-button type="submit">{{ __('ui.actions.save') }}</x-button>
            </form>
        @endcan
    @endif
</x-dynamic-component>
