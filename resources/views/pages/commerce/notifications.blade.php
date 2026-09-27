<x-layouts.dashboard :title="__('commerce.notifications')">
    <x-flash />
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-xl font-bold sm:text-2xl">{{ __('commerce.notifications') }}</h1>
        <form method="POST" action="{{ route('notifications.read') }}">@csrf<button class="text-sm font-semibold text-twende-green">{{ __('commerce.mark_read') }}</button></form>
    </div>
    @if ($notifications->isEmpty())
        <div class="mt-6"><x-empty-state :title="__('commerce.notifications_empty_title')" :description="__('commerce.notifications_empty_body')" /></div>
    @else
        <ul class="mt-6 space-y-3">
            @foreach ($notifications as $notice)
                <li class="rounded-lg border border-twende-line bg-white p-4 dark:border-white/10 dark:bg-twende-night-card {{ $notice->read_at ? 'opacity-70' : '' }}">
                    <p class="font-semibold">{{ $notice->data['title'] ?? '' }}</p>
                    <p class="mt-1 text-sm text-twende-muted">{{ $notice->data['body'] ?? '' }}</p>
                    @if (! empty($notice->data['url']))
                        <a href="{{ $notice->data['url'] }}" class="mt-2 inline-block text-sm font-semibold text-twende-red">{{ __('commerce.tracking') }}</a>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</x-layouts.dashboard>
