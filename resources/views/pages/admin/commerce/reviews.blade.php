<x-layouts.dashboard :title="__('commerce.reviews')">
    <h1 class="text-xl font-bold">{{ __('commerce.reviews') }}</h1>
    @if ($reviews->isEmpty())
        <div class="mt-4"><x-empty-state :title="__('commerce.no_reviews')" /></div>
    @else
        <ul class="mt-4 space-y-2">
            @foreach ($reviews as $review)
                <li class="rounded-lg border border-twende-line bg-white p-3 text-sm dark:border-white/10 dark:bg-twende-night-card">
                    <p class="font-semibold">{{ $review->product?->name }} · {{ $review->rating }}/5</p>
                    <p class="text-twende-muted">{{ $review->user?->name }}</p>
                    <p class="mt-1">{{ $review->body }}</p>
                </li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $reviews->links() }}</div>
    @endif
</x-layouts.dashboard>
