<x-layouts.dashboard :title="__('operations.my_reviews')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('operations.my_reviews') }}</h1>
    <ul class="mt-6 space-y-4">
        @forelse ($reviews as $review)
            <li class="rounded-2xl border border-twende-line p-4 text-sm dark:border-white/10">
                <p class="font-semibold">{{ $review->product?->name }} · {{ $review->user?->name }} · {{ $review->rating }}/5</p>
                <p class="mt-1">{{ $review->body }}</p>
                @if ($review->vendor_reply)
                    <p class="mt-2 text-twende-muted">{{ $review->vendor_reply }}</p>
                @endif
                <form method="POST" action="{{ route('vendor.reviews.reply', $review) }}" class="mt-3 flex gap-2">
                    @csrf
                    <input name="vendor_reply" required class="h-11 min-w-0 flex-1 rounded-full border border-twende-line px-4 dark:border-white/15 dark:bg-twende-night" value="{{ $review->vendor_reply }}">
                    <button class="h-11 rounded-full bg-twende-green px-4 text-sm font-semibold text-white">{{ __('operations.reply') }}</button>
                </form>
            </li>
        @empty
            <li><x-empty-state :title="__('operations.my_reviews')" /></li>
        @endforelse
    </ul>
</x-layouts.dashboard>
