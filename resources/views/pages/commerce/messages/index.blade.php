<x-layouts.dashboard :title="__('commerce.messages')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('commerce.messages') }}</h1>
    @if ($conversations->isEmpty())
        <div class="mt-6"><x-empty-state :title="__('commerce.messages_empty_title')" :description="__('commerce.messages_empty_body')" /></div>
    @else
        <ul class="mt-6 space-y-3">
            @foreach ($conversations as $conversation)
                <li><a href="{{ route('messages.show', $conversation) }}" class="block rounded-2xl border border-twende-line p-4 hover:border-twende-green dark:border-white/10"><span class="font-semibold">{{ $conversation->shop->name }}</span><span class="mt-1 block text-sm text-twende-muted">{{ $conversation->messages->first()?->body }}</span></a></li>
            @endforeach
        </ul>
    @endif
</x-layouts.dashboard>
