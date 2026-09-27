<?php

namespace Tests\Unit;

use App\Support\PhoneNumber;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    public function test_it_strips_spaces_and_keeps_the_international_prefix(): void
    {
        $this->assertSame('+243810000099', PhoneNumber::normalize('+243 81 000 0099'));
    }

    public function test_blank_values_become_null(): void
    {
        $this->assertNull(PhoneNumber::normalize(null));
        $this->assertNull(PhoneNumber::normalize('   '));
    }
}
