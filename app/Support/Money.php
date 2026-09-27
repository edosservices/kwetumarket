<?php

namespace App\Support;

class Money
{
    public static function format(int $minor, string $currency = 'CDF'): string
    {
        $sign = $minor < 0 ? '-' : '';
        $minor = abs($minor);
        $major = intdiv($minor, 100);
        $cents = $minor % 100;
        $formatted = number_format($major, 0, ',', ' ');

        if ($cents > 0) {
            $formatted .= ','.str_pad((string) $cents, 2, '0', STR_PAD_LEFT);
        }

        return $sign.$formatted.' '.$currency;
    }
}
