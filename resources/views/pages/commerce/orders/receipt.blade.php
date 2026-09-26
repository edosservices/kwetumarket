<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $order->number }}</title>
</head>
<body>
    <h1>{{ config('twende.name') }} — {{ __('experience.receipt_title') }}</h1>
    <p>{{ __('experience.paid_confirmed') }}</p>
    <p>{{ $order->number }}</p>
    <p>{{ $order->payment?->reference }}</p>
    <p>{{ __('experience.pay_'.$order->payment_method) }}</p>
    <p>{{ \App\Support\Money::format((int) $order->total, $order->currency) }}</p>
    <p>{{ $order->updated_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
    <ul>
        @foreach ($order->items as $item)
            <li>{{ $item->name }} × {{ $item->quantity }} — {{ \App\Support\Money::format((int) $item->line_total, $order->currency) }}</li>
        @endforeach
    </ul>
</body>
</html>
