<?php

namespace App\Services\Geo;

final class GeoDistance
{
    private const EARTH_METERS = 6371000;

    public static function meters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return 2 * self::EARTH_METERS * asin(min(1, sqrt($a)));
    }

    public static function format(?float $meters): ?string
    {
        if ($meters === null) {
            return null;
        }

        if ($meters < 1000) {
            return (string) (int) round($meters).' m';
        }

        $kilometers = $meters / 1000;
        $decimals = $kilometers < 10 ? 1 : 0;

        return number_format($kilometers, $decimals, ',', ' ').' km';
    }

    /**
     * @return array{min_lat: float, max_lat: float, min_lng: float, max_lng: float}
     */
    public static function box(float $lat, float $lng, float $radiusMeters): array
    {
        $latDelta = $radiusMeters / 111320;
        $lngScale = max(abs(cos(deg2rad($lat))), 0.01);
        $lngDelta = $radiusMeters / (111320 * $lngScale);

        return [
            'min_lat' => $lat - $latDelta,
            'max_lat' => $lat + $latDelta,
            'min_lng' => $lng - $lngDelta,
            'max_lng' => $lng + $lngDelta,
        ];
    }
}
