<x-layouts.dashboard :title="__('operations.my_reviews')">
    <h1 class="text-2xl font-bold">{{ __('operations.my_reviews') }}</h1>
    <ul class="mt-6 space-y-3">
        @forelse ($reviews as $review)
            <li class="rounded-2xl border border-twende-line p-4 text-sm dark:border-white/10">
                <p class="font-semibold">{{ $review->product?->name }} · {{ $review->rating }}/5</p>
                <p class="mt-1">{{ $review->body }}</p>
                @if ($review->vendor_reply)
                    <p class="mt-2 text-twende-muted">{{ __('operations.vendor_reply') }} — {{ $review->vendor_reply }}</p>
                @endif
            </li>
        @empty
            <li><x-empty-state :title="__('operations.my_reviews')" /></li>
        @endforelse
    </ul>
</x-layouts.dashboard>
