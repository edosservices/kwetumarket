<x-layouts.dashboard :title="__('commerce.referral')">
    <h1 class="text-2xl font-bold">{{ __('commerce.referral') }}</h1>
    <p class="mt-3 max-w-xl text-sm text-twende-muted">{{ __('commerce.referral_help') }}</p>
    <p class="mt-4 text-sm">{{ __('commerce.referral_code') }}</p>
    <p class="mt-1 font-mono text-2xl font-bold text-twende-red">{{ $user->referral_code }}</p>
    <ul class="mt-6 space-y-2 text-sm">
        @forelse ($referrals as $referral)
            <li class="rounded-xl border border-twende-line px-3 py-2 dark:border-white/10">{{ $referral->referred?->name }} @if($referral->rewarded_at) · {{ $referral->rewarded_at->format('d/m/Y') }} @endif</li>
        @empty
            <li class="text-twende-muted">{{ __('commerce.messages_empty_body') }}</li>
        @endforelse
    </ul>
</x-layouts.dashboard>
