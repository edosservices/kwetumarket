<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum SocialPlatform: string
{
    use HasValues;

    case Phone = 'phone';
    case Whatsapp = 'whatsapp';
    case Instagram = 'instagram';
    case Tiktok = 'tiktok';
    case Facebook = 'facebook';
    case Email = 'email';
    case Website = 'website';

    /**
     * @return array<int, self>
     */
    public static function formPlatforms(): array
    {
        return [
            self::Whatsapp,
            self::Instagram,
            self::Tiktok,
            self::Facebook,
            self::Website,
        ];
    }
}
