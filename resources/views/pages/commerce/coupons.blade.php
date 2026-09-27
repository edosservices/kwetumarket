<x-layouts.dashboard :title="__('operations.coupons')">
    <h1 class="text-2xl font-bold">{{ __('operations.coupons') }}</h1>
    <ul class="mt-6 space-y-3">
        @forelse ($coupons as $coupon)
            <li class="rounded-2xl border border-twende-line p-4 dark:border-white/10">
                <p class="font-semibold">{{ $coupon->code }}</p>
                <p class="text-sm text-twende-muted">{{ $coupon->type }} · {{ $coupon->value }}</p>
            </li>
        @empty
            <li><x-empty-state :title="__('operations.coupons')" /></li>
        @endforelse
    </ul>
</x-layouts.dashboard>
