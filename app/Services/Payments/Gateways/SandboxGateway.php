<?php

namespace App\Services\Payments\Gateways;

use App\Models\Payment;
use App\Services\Payments\PaymentAuthorization;
use App\Services\Payments\PaymentGateway;

class SandboxGateway implements PaymentGateway
{
    public function code(): string
    {
        return 'sandbox';
    }

    public function authorize(Payment $payment): PaymentAuthorization
    {
        $order = $payment->order;

        if ($order === null || (int) $payment->amount !== (int) $order->total || $payment->amount < 0) {
            return new PaymentAuthorization('failed', null, [
                'live_capture' => false,
                'reason' => 'amount',
            ]);
        }

        return new PaymentAuthorization('paid', 'SANDBOX-'.$order->number, [
            'live_capture' => false,
            'driver' => 'sandbox',
        ]);
    }
}
