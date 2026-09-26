<?php

namespace App\Services\Payments\Gateways;

use App\Models\Payment;
use App\Services\Payments\PaymentAuthorization;
use App\Services\Payments\PaymentGateway;

class HostedGateway implements PaymentGateway
{
    public function __construct(private readonly string $provider) {}

    public function code(): string
    {
        return $this->provider;
    }

    public function authorize(Payment $payment): PaymentAuthorization
    {
        return new PaymentAuthorization('pending', strtoupper($this->provider).'-'.$payment->id, [
            'live_capture' => false,
            'driver' => $this->provider,
            'awaiting_webhook' => true,
        ]);
    }
}
