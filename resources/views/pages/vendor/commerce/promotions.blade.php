<x-layouts.dashboard :title="__('commerce.promotions')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('commerce.promotions') }}</h1>
    <form method="POST" action="{{ route('vendor.promotions.store') }}" class="mt-4 grid max-w-xl gap-3">
        @csrf
        <select name="product_id" class="h-11 rounded-xl border border-twende-line px-3 dark:border-white/15 dark:bg-twende-night">
            @foreach ($products as $product)<option value="{{ $product->id }}">{{ $product->name }}</option>@endforeach
        </select>
        <input name="promotional_price" inputmode="decimal" required placeholder="{{ __('commerce.price') }}" class="h-11 rounded-xl border border-twende-line px-3 dark:border-white/15 dark:bg-twende-night">
        <input type="datetime-local" name="ends_at" required class="h-11 rounded-xl border border-twende-line px-3 dark:border-white/15 dark:bg-twende-night">
        <button class="h-11 rounded-full bg-twende-green px-4 text-sm font-semibold text-white">{{ __('commerce.promotions') }}</button>
    </form>
    @if ($promotions->isEmpty())
        <div class="mt-6"><x-empty-state :title="__('commerce.empty_promotions')" :description="__('commerce.empty_promotions_body')" /></div>
    @endif
</x-layouts.dashboard>
