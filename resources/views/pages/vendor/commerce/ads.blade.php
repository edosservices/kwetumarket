<x-layouts.dashboard :title="__('commerce.ads')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('commerce.ads') }}</h1>
    <form method="POST" action="{{ route('vendor.ads.store') }}" class="mt-4 max-w-xl space-y-3">
        @csrf
        <input name="title" required class="h-11 w-full rounded-xl border border-twende-line px-3 dark:border-white/15 dark:bg-twende-night">
        <textarea name="body" required class="min-h-24 w-full rounded-xl border border-twende-line px-3 py-2 dark:border-white/15 dark:bg-twende-night"></textarea>
        <button class="h-11 rounded-full bg-twende-red px-4 text-sm font-semibold text-white">{{ __('commerce.ads') }}</button>
    </form>
    <ul class="mt-6 space-y-2 text-sm">@foreach ($ads as $ad)<li>{{ $ad->title }} — {{ $ad->status }}</li>@endforeach</ul>
</x-layouts.dashboard>
