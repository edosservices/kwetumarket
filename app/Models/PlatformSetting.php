<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function integer(string $key, int $fallback): int
    {
        $value = static::query()->where('key', $key)->value('value');

        if (! is_string($value) || $value === '' || ! preg_match('/^\d+$/', $value)) {
            return $fallback;
        }

        return (int) $value;
    }

    public static function put(string $key, string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
