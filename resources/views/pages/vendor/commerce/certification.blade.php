<x-layouts.dashboard :title="__('commerce.certification')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('commerce.certification') }}</h1>
    <form method="POST" action="{{ route('vendor.certification.store') }}" class="mt-4 max-w-xl space-y-3">
        @csrf
        <textarea name="note" required minlength="10" class="min-h-24 w-full rounded-xl border border-twende-line px-3 py-2 dark:border-white/15 dark:bg-twende-night"></textarea>
        <button class="h-11 rounded-full bg-twende-green px-4 text-sm font-semibold text-white">{{ __('commerce.certification') }}</button>
    </form>
    <ul class="mt-6 space-y-2 text-sm">
        @foreach ($requests as $request)
            <li>{{ $request->status }} — {{ $request->note }}</li>
        @endforeach
    </ul>
</x-layouts.dashboard>
