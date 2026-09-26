<x-layouts.dashboard :title="__('commerce.wallet')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('commerce.wallet') }}</h1>
    <p class="mt-3 text-3xl font-bold text-twende-green">{{ \App\Support\Money::format((int) ($wallet->balance ?? 0)) }}</p>
    <form method="POST" action="{{ route('vendor.withdrawals.store') }}" class="mt-6 max-w-md space-y-3">
        @csrf
        <label class="block text-sm">{{ __('commerce.withdraw') }}
            <input name="amount" inputmode="decimal" required class="mt-1 h-11 w-full rounded-xl border border-twende-line px-3 dark:border-white/15 dark:bg-twende-night">
        </label>
        <input name="note" class="h-11 w-full rounded-xl border border-twende-line px-3 dark:border-white/15 dark:bg-twende-night" placeholder="{{ __('commerce.notes') }}">
        <button class="h-11 rounded-full bg-twende-green-bright px-5 text-sm font-semibold text-white">{{ __('commerce.withdraw') }}</button>
    </form>
    <ul class="mt-6 space-y-2 text-sm">
        @foreach ($wallet?->entries ?? [] as $entry)
            <li class="flex justify-between rounded-xl bg-twende-light px-3 py-2 dark:bg-white/5"><span>{{ $entry->note }}</span><span>{{ \App\Support\Money::format((int) $entry->amount) }}</span></li>
        @endforeach
        @foreach ($withdrawals as $withdrawal)
            <li class="text-twende-muted">{{ __('commerce.withdraw') }} {{ \App\Support\Money::format((int) $withdrawal->amount) }} — {{ $withdrawal->status }}</li>
        @endforeach
    </ul>
</x-layouts.dashboard>
