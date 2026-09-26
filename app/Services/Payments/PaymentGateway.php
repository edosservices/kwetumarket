<?php

namespace App\Services\Payments;

use App\Models\Payment;

interface PaymentGateway
{
    public function code(): string;

    public function authorize(Payment $payment): PaymentAuthorization;
}
