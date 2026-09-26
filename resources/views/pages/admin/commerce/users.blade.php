<x-layouts.dashboard :title="__('commerce.users')">
    <h1 class="text-2xl font-bold">{{ __('commerce.users') }}</h1>
    <ul class="mt-6 space-y-2 text-sm">
        @foreach ($users as $person)
            <li class="flex flex-wrap justify-between gap-2 rounded-xl border border-twende-line px-3 py-2 dark:border-white/10"><span>{{ $person->name }} · {{ $person->email }}</span><span>{{ $person->getRoleNames()->join(', ') }}</span></li>
        @endforeach
    </ul>
</x-layouts.dashboard>
