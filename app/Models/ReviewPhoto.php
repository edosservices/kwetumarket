<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ReviewPhoto extends Model
{
    protected $fillable = ['review_id', 'disk', 'path'];

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }
}
