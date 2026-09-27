<?php

namespace App\Contracts;

interface OutboundChannel
{
    public function code(): string;

    public function configured(): bool;
}
