<?php

namespace Tests\Feature;

use App\Contracts\SmsGateway;
use App\Enums\OtpPurpose;
use App\Models\User;
use App\Services\Otp\OtpService;
use App\Services\Sms\LogSmsGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtpServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_phone_code_can_be_used_once(): void
    {
        $user = User::factory()->create();
        $service = app(OtpService::class);

        $service->issue('+243810000010', OtpPurpose::PhoneVerification, $user);

        $gateway = app(SmsGateway::class);
        $this->assertInstanceOf(LogSmsGateway::class, $gateway);
        $this->assertSame('+243810000010', $gateway->lastRecipient);
        $this->assertMatchesRegularExpression('/\d{6}/', (string) $gateway->lastMessage);

        preg_match('/(\d{6})/', (string) $gateway->lastMessage, $matches);

        $this->assertTrue($service->verify('+243810000010', OtpPurpose::PhoneVerification, $matches[1]));
        $this->assertNotNull($user->fresh()->phone_verified_at);
        $this->assertFalse($service->verify('+243810000010', OtpPurpose::PhoneVerification, $matches[1]));
    }

    public function test_wrong_codes_are_rejected(): void
    {
        $service = app(OtpService::class);
        $service->issue('+243810000011', OtpPurpose::Login);

        $this->assertFalse($service->verify('+243810000011', OtpPurpose::Login, '000000'));
    }
}
