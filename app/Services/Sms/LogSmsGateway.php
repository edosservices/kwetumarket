<?php

namespace App\Services\Sms;

use App\Contracts\SmsGateway;
use Illuminate\Support\Facades\Log;

class LogSmsGateway implements SmsGateway
{
    public ?string $lastRecipient = null;

    public ?string $lastMessage = null;

    public function send(string $to, string $message): void
    {
        $this->lastRecipient = $to;
        $this->lastMessage = $message;

        Log::info('SMS queued', [
            'to' => $to,
            'length' => strlen($message),
        ]);
    }
}
