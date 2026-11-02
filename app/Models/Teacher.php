<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'teacher_id',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'subject',
        'school_year',
        'status',
        'user_id',
        'is_demo',
    ];

    public function account()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
