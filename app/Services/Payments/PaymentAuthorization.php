<?php

namespace App\Services\Payments;

final class PaymentAuthorization
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public readonly string $status,
        public readonly ?string $reference,
        public readonly array $meta = [],
    ) {}
}
