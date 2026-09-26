<?php

namespace App\Services\Payments;

class PaymentCatalog
{
    /**
     * @return list<array{code: string, label: string, sandbox: bool, pending: bool}>
     */
    public function options(): array
    {
        $options = [[
            'code' => 'cod',
            'label' => __('experience.pay_cod'),
            'sandbox' => false,
            'pending' => false,
        ]];

        if (config('twende.commerce.payment_driver') === 'sandbox') {
            $options[] = [
                'code' => 'sandbox',
                'label' => __('experience.pay_sandbox'),
                'sandbox' => true,
                'pending' => false,
            ];
        }

        foreach (['mpesa', 'airtel', 'orange', 'afrimoney', 'card'] as $code) {
            if (! $this->configured($code)) {
                continue;
            }

            $options[] = [
                'code' => $code,
                'label' => __('experience.pay_'.$code),
                'sandbox' => false,
                'pending' => true,
            ];
        }

        return $options;
    }

    /**
     * @return list<string>
     */
    public function codes(): array
    {
        return array_column($this->options(), 'code');
    }

    public function allows(string $code): bool
    {
        return in_array($code, $this->codes(), true);
    }

    public function configured(string $code): bool
    {
        return filter_var(config("twende.payments.{$code}.enabled"), FILTER_VALIDATE_BOOL)
            && filled(config("twende.payments.{$code}.key"))
            && filled(config("twende.payments.{$code}.secret"));
    }
}
