<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'subject_name',
        'subject_code',
        'subject_type',
        'status',
    ];

    public function gradeLevels(): HasMany
    {
        return $this->hasMany(GradeLevelSubject::class);
    }

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(
            TeachingAssignment::class,
            'subject',
            'subject_name'
        );
    }
}
