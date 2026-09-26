<x-layouts.dashboard :title="__('commerce.refund')">
    <x-flash />
    <h1 class="text-2xl font-bold">{{ __('commerce.refund') }}</h1>
    <p class="mt-2 text-sm text-twende-muted">{{ __('commerce.refund_approved') }}</p>
    <ul class="mt-6 space-y-3 text-sm">
        @foreach ($refunds as $refund)
            <li class="rounded-2xl border border-twende-line p-4 dark:border-white/10">
                {{ $refund->order->number }} · {{ \App\Support\Money::format((int) $refund->amount, $refund->order->currency) }} · {{ $refund->status }}
                <p>{{ $refund->reason }}</p>
                @if ($refund->status === 'pending')
                    <form method="POST" action="{{ route('admin.refunds.update', $refund) }}" class="mt-2 flex gap-2">
                        @csrf
                        <button name="decision" value="approved" class="h-10 rounded-full bg-twende-green px-3 text-white">{{ __('commerce.approve') }}</button>
                        <button name="decision" value="rejected" class="h-10 rounded-full bg-twende-red px-3 text-white">{{ __('commerce.reject') }}</button>
                    </form>
                @endif
            </li>
        @endforeach
    </ul>
</x-layouts.dashboard>
