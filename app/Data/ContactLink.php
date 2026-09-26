<?php

namespace App\Data;

final class ContactLink
{
    public function __construct(
        public string $platform,
        public string $label,
        public string $href,
        public bool $external,
    ) {}
}
