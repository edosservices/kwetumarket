<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = ['base', 'quote', 'minor_per_unit', 'quoted_at'];

    protected function casts(): array
    {
        return [
            'minor_per_unit' => 'integer',
            'quoted_at' => 'datetime',
        ];
    }
}
