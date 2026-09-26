<x-layouts.dashboard :title="__('commerce.certification')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('commerce.certification') }}</h1>
    <ul class="mt-6 space-y-3 text-sm">
        @foreach ($requests as $item)
            <li class="rounded-2xl border border-twende-line p-4 dark:border-white/10">
                {{ $item->vendor?->user?->name }} · {{ $item->status }}
                <p>{{ $item->note }}</p>
                @if ($item->status === 'pending')
                    <form method="POST" action="{{ route('admin.certifications.update', $item) }}" class="mt-2 flex gap-2">
                        @csrf
                        <button name="decision" value="approved" class="h-10 rounded-full bg-twende-green px-3 text-white">{{ __('commerce.approve') }}</button>
                        <button name="decision" value="rejected" class="h-10 rounded-full bg-twende-red px-3 text-white">{{ __('commerce.reject') }}</button>
                    </form>
                @endif
            </li>
        @endforeach
    </ul>
</x-layouts.dashboard>
