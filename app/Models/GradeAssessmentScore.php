<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeAssessmentScore extends Model
{
    protected $fillable = [
        'grade_record_id',
        'assessment_code',
        'assessment_group',
        'score',
        'highest_possible_score',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'highest_possible_score' => 'decimal:2',
        ];
    }

    public function gradeRecord(): BelongsTo
    {
        return $this->belongsTo(GradeRecord::class);
    }
}
