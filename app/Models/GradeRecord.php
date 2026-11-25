<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeRecord extends Model
{
    protected $fillable = [
        'student_id',
        'teaching_assignment_id',
        'school_year',
        'term',

        'ww_ps',
        'ww_ws',

        'pt_ps',
        'pt_ws',

        'ex_ps',
        'ex_ws',

        'initial_grade',
        'term_grade',
        'descriptor',

        'status',
        'submitted_at',
        'adviser_reviewed_at',
        'adviser_return_reason',
        'admin_reviewed_at',
        'admin_return_reason',
    ];

    protected function casts(): array
    {
        return [
            'term' => 'integer',
            'ww_ps' => 'decimal:2',
            'ww_ws' => 'decimal:2',
            'pt_ps' => 'decimal:2',
            'pt_ws' => 'decimal:2',
            'ex_ps' => 'decimal:2',
            'ex_ws' => 'decimal:2',
            'initial_grade' => 'decimal:2',
            'term_grade' => 'decimal:2',
            'submitted_at' => 'datetime',
            'adviser_reviewed_at' => 'datetime',
            'admin_reviewed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teachingAssignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class);
    }

    public function assessmentScores(): HasMany
    {
        return $this->hasMany(GradeAssessmentScore::class);
    }
}
