<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvisoryAssignment extends Model
{
    protected $fillable = [
        'faculty_id',
        'grade_level',
        'section',
        'school_year',
    ];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }
}
