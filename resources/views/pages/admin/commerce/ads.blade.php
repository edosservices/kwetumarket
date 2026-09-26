<x-layouts.dashboard :title="__('commerce.ads')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('commerce.ads') }}</h1>
    <ul class="mt-6 space-y-3 text-sm">
        @foreach ($ads as $ad)
            <li class="rounded-2xl border border-twende-line p-4 dark:border-white/10">
                <p class="font-semibold">{{ $ad->title }} · {{ $ad->status }}</p>
                <p>{{ $ad->body }}</p>
                @if ($ad->status === 'pending')
                    <form method="POST" action="{{ route('admin.ads.update', $ad) }}" class="mt-2 flex gap-2">
                        @csrf
                        <button name="decision" value="approved" class="h-10 rounded-full bg-twende-green px-3 text-white">{{ __('commerce.approve') }}</button>
                        <button name="decision" value="rejected" class="h-10 rounded-full bg-twende-red px-3 text-white">{{ __('commerce.reject') }}</button>
                    </form>
                @endif
            </li>
        @endforeach
    </ul>
</x-layouts.dashboard>
