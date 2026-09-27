<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $fillable = ['order_id', 'payment_id', 'amount_minor', 'currency', 'status'];
}
