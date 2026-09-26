<x-layouts.dashboard :title="$order->number">
    <x-flash />
    <p class="text-sm text-twende-muted">{{ __('commerce.orders') }}</p>
    <h1 class="text-2xl font-bold">{{ $order->number }}</h1>
    <div class="mt-3 flex flex-wrap gap-2">
        <x-badge variant="green">{{ __('commerce.order_statuses.'.$order->status) }}</x-badge>
        <x-badge>{{ __('commerce.delivery_statuses.'.($order->delivery->status ?? 'pending')) }}</x-badge>
        <x-badge>{{ $order->payment_method === 'sandbox' ? __('commerce.pay_sandbox') : __('commerce.pay_cod') }}</x-badge>
    </div>
    @if ($order->payment_method === 'sandbox')
        <p class="mt-3 text-sm text-twende-muted">{{ __('commerce.sandbox_note') }}</p>
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
            @if ($order->status !== 'cancelled')
                <form method="POST" action="{{ route('orders.review', $order) }}" class="space-y-2">
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
                    <button class="h-11 rounded-full bg-twende-green px-4 text-sm font-semibold text-white">{{ __('commerce.review') }}</button>
                </form>
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
