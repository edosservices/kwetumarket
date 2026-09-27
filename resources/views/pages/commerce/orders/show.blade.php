<x-layouts.dashboard :title="$order->number">
    <x-flash />
    <p class="text-sm text-twende-muted">{{ __('commerce.orders') }}</p>
    <h1 class="text-2xl font-bold">{{ $order->number }}</h1>
    <div class="mt-3 flex flex-wrap gap-2">
        <x-badge variant="green">{{ __('commerce.order_statuses.'.$order->status) }}</x-badge>
        <x-badge>{{ __('commerce.delivery_statuses.'.($order->delivery->status ?? 'pending')) }}</x-badge>
        <x-badge>{{ __('experience.pay_'.$order->payment_method) }}</x-badge>
        <x-badge>{{ $order->payment_status }}</x-badge>
    </div>
    @if ($order->payment_method === 'sandbox')
        <p class="mt-3 text-sm text-twende-muted">{{ __('commerce.sandbox_note') }}</p>
    @endif
    @if ($order->payment_status === 'pending')
        <p class="mt-3 text-sm font-semibold">{{ __('experience.payment_pending') }}</p>
    @endif
    @if ($order->payment_status === 'failed')
        <p class="mt-3 text-sm font-semibold text-twende-red">{{ __('operations.payment_failed') }}</p>
        @if ((int) $order->user_id === (int) auth()->id())
            <form method="POST" action="{{ route('orders.retry', $order) }}" class="mt-3 flex flex-wrap gap-2">
                @csrf
                <select name="payment_method" class="h-11 rounded-xl border border-twende-line px-3 dark:border-white/15 dark:bg-twende-night">
                    @foreach (app(\App\Services\Payments\PaymentCatalog::class)->options() as $method)
                        @if ($method['code'] !== 'cod')
                            <option value="{{ $method['code'] }}">{{ $method['label'] }}</option>
                        @endif
                    @endforeach
                </select>
                <button class="h-11 rounded-full bg-twende-green px-4 text-sm font-semibold text-white">{{ __('operations.retry') }}</button>
            </form>
        @endif
    @endif
    @if ($order->delivery?->agent && in_array($order->delivery->status, ['accepted', 'departed', 'en_route', 'arrived', 'delivered'], true))
        <p class="mt-3 text-sm">{{ __('operations.courier') }} : {{ $order->delivery->agent->name }} @if($order->delivery->agent->courierProfile?->vehicle_type) · {{ $order->delivery->agent->courierProfile->vehicle_type }} @endif</p>
        @php $left = $order->events->firstWhere('status', 'departed'); @endphp
        @if ($left)
            <p class="text-sm">{{ __('operations.departure') }} : {{ $left->created_at->timezone(config('app.timezone'))->format('d/m H:i') }}</p>
        @endif
        <p class="text-xs text-twende-muted">{{ __('operations.no_live_position') }}</p>
    @endif
    @if ($order->payment_status === 'paid')
        <p class="mt-3 text-sm font-semibold text-twende-green">{{ __('experience.paid_confirmed') }}</p>
        @if ($order->payment?->reference)
            <p class="text-sm">{{ $order->payment->reference }} · {{ \App\Support\Money::format((int) $order->payment->amount, $order->currency) }} · {{ $order->payment->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
        @endif
        <a href="{{ route('orders.receipt', $order) }}" class="mt-2 inline-flex text-sm font-semibold text-twende-green">{{ __('experience.receipt') }}</a>
    @endif
    <p class="mt-4 text-sm">{{ $order->address }}, {{ $order->city }} · {{ $order->phone }}</p>
    @if ($order->delivery?->eta_at)
        <p class="mt-2 text-sm font-semibold text-twende-green">{{ __('commerce.eta', ['time' => $order->delivery->eta_at->timezone(config('app.timezone'))->format('d/m H:i')]) }}</p>
    @endif
    <ul class="mt-6 divide-y divide-twende-line rounded-2xl border border-twende-line dark:divide-white/10 dark:border-white/10">
        @foreach ($order->items as $item)
            <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                <span class="font-medium">{{ $item->name }} @if($item->variant_name) · {{ $item->variant_name }} @endif × {{ $item->quantity }}</span>
                <span>{{ \App\Support\Money::format((int) $item->line_total, $order->currency) }}</span>
            </li>
        @endforeach
    </ul>
    <dl class="mt-4 space-y-1 text-sm">
        <div class="flex justify-between"><dt>{{ __('commerce.subtotal') }}</dt><dd>{{ \App\Support\Money::format((int) $order->subtotal, $order->currency) }}</dd></div>
        <div class="flex justify-between"><dt>{{ __('commerce.discount') }}</dt><dd>-{{ \App\Support\Money::format((int) $order->discount, $order->currency) }}</dd></div>
        <div class="flex justify-between"><dt>{{ __('commerce.delivery') }}</dt><dd>{{ \App\Support\Money::format((int) $order->delivery_fee, $order->currency) }}</dd></div>
        <div class="flex justify-between"><dt>{{ __('commerce.tax') }}</dt><dd>{{ \App\Support\Money::format((int) $order->tax, $order->currency) }}</dd></div>
        <div class="flex justify-between font-bold"><dt>{{ __('commerce.total') }}</dt><dd>{{ \App\Support\Money::format((int) $order->total, $order->currency) }}</dd></div>
    </dl>
    <ol class="mt-6 space-y-2 text-sm">
        @foreach ($order->events as $event)
            <li class="rounded-xl bg-twende-light px-3 py-2 dark:bg-white/5">{{ $event->created_at->timezone(config('app.timezone'))->format('d/m H:i') }} — {{ $event->note ?: $event->status }}</li>
        @endforeach
    </ol>
    @if ((int) $order->user_id === (int) auth()->id())
        <div class="mt-6 grid gap-4 lg:grid-cols-2">
            @if (in_array($order->delivery?->status, ['pending', 'assigned', 'accepted'], true))
                <form method="POST" action="{{ route('orders.cancel', $order) }}">@csrf<button class="h-11 rounded-full bg-twende-red px-4 text-sm font-semibold text-white">{{ __('commerce.cancel') }}</button></form>
            @endif
            @if ($order->status === 'delivered')
                <form method="POST" action="{{ route('orders.review', $order) }}" enctype="multipart/form-data" class="space-y-2">
                    @csrf
                    <label class="block text-sm">{{ __('commerce.review') }}
                        <select name="product_id" class="mt-1 h-11 w-full rounded-xl border border-twende-line px-3 dark:border-white/15 dark:bg-twende-night">
                            @foreach ($order->items as $item)
                                <option value="{{ $item->product_id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm">{{ __('commerce.rating') }}
                        <input type="number" name="rating" min="1" max="5" value="5" class="mt-1 h-11 w-full rounded-xl border border-twende-line px-3 dark:border-white/15 dark:bg-twende-night">
                    </label>
                    <textarea name="body" required minlength="10" class="min-h-20 w-full rounded-xl border border-twende-line px-3 py-2 dark:border-white/15 dark:bg-twende-night"></textarea>
                    <label class="block text-sm">{{ __('experience.review_photo') }}
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="mt-1 block text-sm">
                    </label>
                    <p class="text-xs text-twende-muted">{{ __('experience.verified_purchase') }}</p>
                    <button class="h-11 rounded-full bg-twende-green px-4 text-sm font-semibold text-white">{{ __('commerce.review') }}</button>
                </form>
            @endif
            @if (in_array($order->status, ['delivered', 'shipped'], true) && $order->payment_status === 'paid')
                <form method="POST" action="{{ route('orders.return', $order) }}" enctype="multipart/form-data" class="space-y-2">
                    @csrf
                    <label class="block text-sm">{{ __('operations.return') }}<textarea name="reason" required minlength="10" class="mt-1 min-h-20 w-full rounded-xl border border-twende-line px-3 py-2 dark:border-white/15 dark:bg-twende-night"></textarea></label>
                    <input type="file" name="evidence" accept="image/jpeg,image/png,image/webp,application/pdf" class="text-sm">
                    <button class="h-11 rounded-full border border-twende-line px-4 text-sm font-semibold dark:border-white/15">{{ __('operations.return') }}</button>
                </form>
            @endif
            @if ($order->status !== 'cancelled')
                <form method="POST" action="{{ route('orders.dispute', $order) }}" class="space-y-2">
                    @csrf
                    <label class="block text-sm">{{ __('commerce.dispute') }}<textarea name="reason" required minlength="10" class="mt-1 min-h-20 w-full rounded-xl border border-twende-line px-3 py-2 dark:border-white/15 dark:bg-twende-night"></textarea></label>
                    <button class="h-11 rounded-full border border-twende-line px-4 text-sm font-semibold dark:border-white/15">{{ __('commerce.dispute') }}</button>
                </form>
                <form method="POST" action="{{ route('orders.refund', $order) }}" class="space-y-2">
                    @csrf
                    <label class="block text-sm">{{ __('commerce.refund') }}<textarea name="reason" required minlength="10" class="mt-1 min-h-20 w-full rounded-xl border border-twende-line px-3 py-2 dark:border-white/15 dark:bg-twende-night"></textarea></label>
                    <button class="h-11 rounded-full border border-twende-line px-4 text-sm font-semibold dark:border-white/15">{{ __('commerce.refund') }}</button>
                </form>
            @endif
        </div>
    @endif
</x-layouts.dashboard>
