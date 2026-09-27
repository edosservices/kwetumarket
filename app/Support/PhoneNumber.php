<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class PhoneNumber
{
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $normalized = preg_replace('/\s+/', '', trim($phone)) ?? '';

        return $normalized === '' ? null : $normalized;
    }

    /**
     * @return array<int, mixed>
     */
    public static function rules(?int $ignoreUserId = null): array
    {
        $unique = Rule::unique('users', 'phone');

        if ($ignoreUserId !== null) {
            $unique->ignore($ignoreUserId);
        }

        return [
            'nullable',
            'string',
            'max:20',
            'regex:/^\+?[0-9]{8,15}$/',
            $unique,
        ];
    }
}
