<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    protected $fillable = [
        'name',
        'code',
        'credits'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
