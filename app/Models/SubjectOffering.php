<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectOffering extends Model
{
    protected $fillable = ['subject_id', 'teacher_id', 'section', 'meeting_days', 'starts_at', 'ends_at', 'room'];
    public function subject() { return $this->belongsTo(Subject::class); }
    public function teacher() { return $this->belongsTo(Teacher::class); }
    public function grades() { return $this->hasMany(GradeRecord::class); }

    // No invented enrolments: only explicit matching section or admin-assigned ALL.
    public function studentsQuery()
    {
        $subject = $this->subject;
        return Student::query()->where('school_year', $subject->school_year)
            ->where('grade_level', $subject->grade_level)
            ->when($this->section !== 'ALL', fn ($q) => $q->where('section', $this->section));
    }
}
