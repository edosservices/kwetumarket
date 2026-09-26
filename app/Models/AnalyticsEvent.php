<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    protected $fillable = ['name', 'user_id', 'subject_type', 'subject_id'];
}
