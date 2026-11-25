<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingAssignment extends Model
{
    protected $fillable = [
        'faculty_id',
        'grade_level',
        'subject',
        'component',
        'school_year',
    ];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function gradeRecords()
    {
        return $this->hasMany(GradeRecord::class);
    }
}
