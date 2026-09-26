<x-layouts.dashboard :title="__('commerce.withdraw')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('commerce.withdraw') }}</h1>
    <ul class="mt-6 space-y-3 text-sm">
        @foreach ($withdrawals as $withdrawal)
            <li class="rounded-2xl border border-twende-line p-4 dark:border-white/10">
                {{ $withdrawal->user?->name }} · {{ \App\Support\Money::format((int) $withdrawal->amount) }} · {{ $withdrawal->status }}
                @if ($withdrawal->status === 'pending')
                    <form method="POST" action="{{ route('admin.withdrawals.update', $withdrawal) }}" class="mt-2 flex gap-2">
                        @csrf
                        <button name="decision" value="paid" class="h-10 rounded-full bg-twende-green px-3 text-white">{{ __('commerce.pay') }}</button>
                        <button name="decision" value="rejected" class="h-10 rounded-full bg-twende-red px-3 text-white">{{ __('commerce.reject') }}</button>
                    </form>
                @endif
            </li>
        @endforeach
    </ul>
</x-layouts.dashboard>
