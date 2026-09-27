<x-dynamic-component :component="'layouts.'.$area" :title="__('ui.modules.notifications')">
    <h1 class="text-2xl font-bold">{{ __('ui.modules.notifications') }}</h1>
    <div class="mt-6 grid gap-3">
        @forelse ($notifications as $notification)
            <article class="rounded-2xl border border-twende-line px-4 py-3 dark:border-white/10">
                <p class="font-medium">{{ $notification->data['title'] ?? __('ui.modules.notifications') }}</p>
                @if (! empty($notification->data['body']))
                    <p class="mt-1 text-sm text-twende-muted">{{ $notification->data['body'] }}</p>
                @endif
            </article>
        @empty
            <x-empty-state :title="__('ui.modules.empty')" />
        @endforelse
    </div>
</x-dynamic-component>
