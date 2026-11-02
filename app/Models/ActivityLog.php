<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = [
        'before' => 'array', 'after' => 'array', 'details' => 'array',
        'created_at' => 'datetime',
    ];
}
