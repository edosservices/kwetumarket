<?php

namespace App\Support;

final class NearbyQuery
{
    /**
     * @return array<int, string>
     */
    public static function sorts(): array
    {
        return ['nearest', 'price', 'stock', 'match', 'promotion', 'popular'];
    }

    public static function radius(mixed $value): float
    {
        $selected = is_numeric($value) ? (float) $value : null;

        foreach (config('twende.nearby.radii_km') as $radius) {
            if ($selected !== null && abs((float) $radius - $selected) < 0.001) {
                return (float) $radius;
            }
        }

        return (float) config('twende.nearby.default_radius_km');
    }

    public static function sort(mixed $value, string $default = 'match'): string
    {
        $value = is_string($value) ? $value : $default;

        return in_array($value, self::sorts(), true) ? $value : $default;
    }

    public static function coordinate(mixed $value, float $min, float $max): ?float
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        if ($number < $min || $number > $max) {
            return null;
        }

        return $number;
    }
}
