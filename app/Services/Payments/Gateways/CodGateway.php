<?php

namespace App\Services\Payments\Gateways;

use App\Models\Payment;
use App\Services\Payments\PaymentAuthorization;
use App\Services\Payments\PaymentGateway;

class CodGateway implements PaymentGateway
{
    public function code(): string
    {
        return 'cod';
    }

    public function authorize(Payment $payment): PaymentAuthorization
    {
        return new PaymentAuthorization('unpaid', null, [
            'live_capture' => false,
            'driver' => 'cod',
        ]);
    }
}
