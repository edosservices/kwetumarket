<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Montants entiers dans l'unité monétaire minimale.
 * CDF et USD sont stockés avec 2 décimales (centimes), jamais en float.
 */
final class Money
{
    public const EXPONENT = 2;

    public const FACTOR = 100;

    public static function toMinor(string $major): int
    {
        $normalized = str_replace([' ', ','], ['', '.'], trim($major));

        if (! preg_match('/^\d+(\.\d{1,2})?$/', $normalized)) {
            throw new InvalidArgumentException('Invalid monetary amount.');
        }

        [$whole, $fraction] = array_pad(explode('.', $normalized, 2), 2, '0');
        $fraction = str_pad(substr($fraction, 0, self::EXPONENT), self::EXPONENT, '0');

        return ((int) $whole * self::FACTOR) + (int) $fraction;
    }

    public static function format(int $minor, string $currency = 'CDF'): string
    {
        $negative = $minor < 0;
        $minor = abs($minor);
        $whole = intdiv($minor, self::FACTOR);
        $fraction = $minor % self::FACTOR;
        $formatted = number_format($whole, 0, ',', ' ').','.str_pad((string) $fraction, self::EXPONENT, '0', STR_PAD_LEFT);

        return ($negative ? '-' : '').$formatted.' '.$currency;
    }

    public static function toInput(int $minor): string
    {
        $whole = intdiv($minor, self::FACTOR);
        $fraction = abs($minor) % self::FACTOR;

        return $whole.'.'.str_pad((string) $fraction, self::EXPONENT, '0', STR_PAD_LEFT);
    }

    public static function amount(int $minor): string
    {
        $negative = $minor < 0;
        $minor = abs($minor);
        $whole = intdiv($minor, self::FACTOR);
        $fraction = $minor % self::FACTOR;

        return ($negative ? '-' : '').number_format($whole, 0, ',', ' ').','.str_pad((string) $fraction, self::EXPONENT, '0', STR_PAD_LEFT);
    }
}
