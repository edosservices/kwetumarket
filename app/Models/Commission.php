<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = ['order_id', 'vendor_id', 'amount_minor', 'currency'];
}
