<x-layouts.dashboard :title="__('commerce.dispute')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('commerce.dispute') }}</h1>
    <ul class="mt-6 space-y-4 text-sm">
        @foreach ($disputes as $dispute)
            <li class="rounded-2xl border border-twende-line p-4 dark:border-white/10">
                <p class="font-semibold">{{ $dispute->order->number }} · {{ $dispute->status }}</p>
                <p class="mt-1">{{ $dispute->reason }}</p>
                @if ($dispute->status === 'open')
                    <form method="POST" action="{{ route('admin.disputes.update', $dispute) }}" class="mt-2 space-y-2">
                        @csrf
                        <textarea name="resolution" required minlength="5" class="min-h-16 w-full rounded-xl border border-twende-line px-3 py-2 dark:border-white/15 dark:bg-twende-night"></textarea>
                        <button class="h-10 rounded-full bg-twende-green px-4 text-white">{{ __('commerce.approve') }}</button>
                    </form>
                @else
                    <p class="mt-2 text-twende-muted">{{ $dispute->resolution }}</p>
                @endif
            </li>
        @endforeach
    </ul>
</x-layouts.dashboard>
