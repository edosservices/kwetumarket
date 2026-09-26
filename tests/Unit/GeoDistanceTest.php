<?php

namespace Tests\Unit;

use App\Services\Geo\GeoDistance;
use PHPUnit\Framework\TestCase;

class GeoDistanceTest extends TestCase
{
    public function test_distance_between_close_points_is_about_one_kilometre(): void
    {
        $meters = GeoDistance::meters(0, 0, 0, 0.01);

        $this->assertGreaterThan(1100, $meters);
        $this->assertLessThan(1130, $meters);
    }

    public function test_same_point_is_zero(): void
    {
        $this->assertSame(0.0, GeoDistance::meters(-4.31, 15.31, -4.31, 15.31));
    }

    public function test_format_uses_meters_then_kilometres(): void
    {
        $this->assertSame('850 m', GeoDistance::format(850));
        $this->assertSame('1,4 km', GeoDistance::format(1400));
        $this->assertSame('4,8 km', GeoDistance::format(4800));
        $this->assertSame('12 km', GeoDistance::format(12000));
    }
}
