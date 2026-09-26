<x-layouts.dashboard :title="__('commerce.settings')">
    <h1 class="text-2xl font-bold">{{ __('commerce.settings') }}</h1>
    <p class="mt-3 max-w-2xl text-sm text-twende-muted">{{ __('commerce.settings_note') }}</p>
    <dl class="mt-6 max-w-xl divide-y divide-twende-line rounded-2xl border border-twende-line dark:divide-white/10 dark:border-white/10">
        @foreach ($settings as $key => $value)
            <div class="flex justify-between gap-3 px-4 py-3 text-sm"><dt class="font-medium">{{ $key }}</dt><dd>{{ $value }}</dd></div>
        @endforeach
    </dl>
</x-layouts.dashboard>
