<x-layouts.dashboard :title="__('ui.dashboard.payments')">
    <h1 class="text-xl font-bold">{{ __('ui.dashboard.payments') }}</h1>
    @if ($payments->isEmpty())
        <div class="mt-4"><x-empty-state :title="__('ui.dashboard.no_sales')" /></div>
    @else
        <div class="mt-4 overflow-x-auto rounded-lg border border-twende-line bg-white dark:border-white/10 dark:bg-twende-night-card">
            <table class="w-full min-w-[40rem] text-left text-sm">
                <thead class="text-xs uppercase text-twende-muted"><tr><th class="px-3 py-2">{{ __('commerce.orders') }}</th><th class="px-3 py-2">{{ __('ui.dashboard.payments') }}</th><th class="px-3 py-2">{{ __('commerce.total') }}</th><th class="px-3 py-2">{{ __('ui.catalog.status') }}</th></tr></thead>
                <tbody>
                    @foreach ($payments as $payment)
                        <tr class="border-t border-twende-line dark:border-white/10">
                            <td class="px-3 py-2 font-semibold">{{ $payment->order?->number }}</td>
                            <td class="px-3 py-2">{{ $payment->provider }}</td>
                            <td class="px-3 py-2">{{ \App\Support\Money::format((int) $payment->amount, $payment->order?->currency ?: 'CDF') }}</td>
                            <td class="px-3 py-2"><x-badge>{{ $payment->status }}</x-badge></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $payments->links() }}</div>
    @endif
</x-layouts.dashboard>
