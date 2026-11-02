<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'first_name',
        'middle_name',
        'last_name',
        'sex',
        'grade_level',
        'section',
        'school_year',
        'email',
        'status',
        'user_id',
        'is_demo',
    ];

    public function account()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
