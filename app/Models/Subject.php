<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'subject_name',
        'subject_code',
        'subject_type',
        'status',
    ];

    
    public function gradeLevels()
    {
        return $this->hasMany(GradeLevelSubject::class);
    }
}