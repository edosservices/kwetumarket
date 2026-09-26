@props(['quote'])

<dl class="space-y-2 text-sm">
    <div class="flex justify-between gap-3"><dt>{{ __('commerce.subtotal') }}</dt><dd>{{ \App\Support\Money::format($quote->subtotal, $quote->currency) }}</dd></div>
    <div class="flex justify-between gap-3"><dt>{{ __('commerce.discount') }}</dt><dd>-{{ \App\Support\Money::format($quote->discount, $quote->currency) }}</dd></div>
    <div class="flex justify-between gap-3"><dt>{{ __('commerce.delivery') }}</dt><dd>{{ \App\Support\Money::format($quote->deliveryFee, $quote->currency) }}</dd></div>
    <div class="flex justify-between gap-3"><dt>{{ __('commerce.tax') }}</dt><dd>{{ \App\Support\Money::format($quote->tax, $quote->currency) }}</dd></div>
    <div class="flex justify-between gap-3 text-twende-muted"><dt>{{ __('commerce.commission') }}</dt><dd>{{ \App\Support\Money::format($quote->commission, $quote->currency) }}</dd></div>
    <p class="text-xs text-twende-muted">{{ __('commerce.commission_note') }}</p>
    <div class="flex justify-between gap-3 border-t border-twende-line pt-3 text-base font-bold dark:border-white/10"><dt>{{ __('commerce.total') }}</dt><dd>{{ \App\Support\Money::format($quote->total, $quote->currency) }}</dd></div>
</dl>
@if ($quote->blocked)
    <p class="mt-3 text-sm font-semibold text-twende-red" role="alert">{{ __('commerce.stock_short') }}</p>
@endif
