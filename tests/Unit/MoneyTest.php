<?php

namespace Tests\Unit;

use App\Support\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_major_amounts_become_minor_units_without_floats(): void
    {
        $this->assertSame(145000000, Money::toMinor('1450000'));
        $this->assertSame(150050, Money::toMinor('1500.50'));
        $this->assertSame(150050, Money::toMinor('1 500,50'));
        $this->assertSame('1 500,50 CDF', Money::format(150050, 'CDF'));
        $this->assertSame('1500.50', Money::toInput(150050));
    }

    public function test_invalid_amounts_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::toMinor('12.345');
    }
}
