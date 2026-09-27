<x-layouts.dashboard :title="$conversation->shop->name">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ $conversation->shop->name }}</h1>
    <ul class="mt-6 space-y-3">
        @foreach ($conversation->messages as $message)
            <li class="max-w-xl rounded-2xl px-4 py-3 text-sm {{ (int) $message->user_id === (int) auth()->id() ? 'ml-auto bg-twende-green text-white' : 'bg-twende-light dark:bg-white/10' }}">
                <p class="text-xs opacity-80">{{ $message->user->name }}</p>
                <p>{{ $message->body }}</p>
                @if ($message->attachment_path)
                    <a class="mt-1 inline-block underline" href="{{ \Illuminate\Support\Facades\Storage::disk($message->attachment_disk ?: 'public')->url($message->attachment_path) }}">{{ __('operations.attachment') }}</a>
                @endif
            </li>
        @endforeach
    </ul>
    <form method="POST" action="{{ route('messages.reply', $conversation) }}" enctype="multipart/form-data" class="mt-6 flex flex-wrap gap-2">
        @csrf
        <label class="sr-only" for="body">{{ __('commerce.write') }}</label>
        <input id="body" name="body" required class="h-11 min-w-0 flex-1 rounded-full border border-twende-line px-4 dark:border-white/15 dark:bg-twende-night">
        <input type="file" name="attachment" accept="image/jpeg,image/png,image/webp,application/pdf" class="text-sm">
        <button class="h-11 rounded-full bg-twende-red px-4 text-sm font-semibold text-white">{{ __('commerce.write') }}</button>
    </form>
</x-layouts.dashboard>
