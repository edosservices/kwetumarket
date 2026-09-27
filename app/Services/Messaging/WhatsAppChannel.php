<?php

namespace App\Services\Messaging;

use App\Contracts\OutboundChannel;

class WhatsAppChannel implements OutboundChannel
{
    public function code(): string
    {
        return 'whatsapp';
    }

    public function configured(): bool
    {
        return filled(config('twende.whatsapp.token')) && filled(config('twende.whatsapp.phone_id'));
    }
}
