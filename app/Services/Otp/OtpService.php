<?php

namespace App\Services\Otp;

use App\Contracts\SmsGateway;
use App\Enums\OtpPurpose;
use App\Models\OtpChallenge;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class OtpService
{
    public function __construct(private readonly SmsGateway $sms) {}

    public function issue(string $identifier, OtpPurpose $purpose, ?User $user = null, string $channel = 'sms'): OtpChallenge
    {
        $code = $this->generateCode();

        $challenge = OtpChallenge::query()->create([
            'user_id' => $user?->id,
            'identifier' => $identifier,
            'channel' => $channel,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes((int) config('twende.otp.ttl_minutes')),
            'attempts' => 0,
        ]);

        if ($channel === 'sms') {
            $this->sms->send($identifier, __('ui.otp.message', ['code' => $code]));
        }

        return $challenge;
    }

    public function verify(string $identifier, OtpPurpose $purpose, string $code): bool
    {
        $challenge = OtpChallenge::query()
            ->where('identifier', $identifier)
            ->where('purpose', $purpose->value)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if ($challenge === null) {
            return false;
        }

        if ($challenge->attempts >= (int) config('twende.otp.max_attempts')) {
            return false;
        }

        if (! Hash::check($code, $challenge->code_hash)) {
            $challenge->increment('attempts');

            return false;
        }

        $challenge->forceFill(['consumed_at' => now()])->save();

        if ($purpose === OtpPurpose::PhoneVerification && $challenge->user !== null) {
            $challenge->user->forceFill([
                'phone' => $identifier,
                'phone_verified_at' => now(),
            ])->save();
        }

        return true;
    }

    private function generateCode(): string
    {
        $length = (int) config('twende.otp.length', 6);
        $max = (10 ** $length) - 1;
        $min = 10 ** ($length - 1);

        return (string) random_int($min, $max);
    }
}
