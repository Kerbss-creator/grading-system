<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeLevelSubject extends Model
{
    protected $fillable = [
        'subject_id',
        'grade_level',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}