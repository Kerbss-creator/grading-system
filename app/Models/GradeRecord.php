<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeRecord extends Model
{
    protected $fillable = ['student_id','subject_offering_id','quarter','grade','status',
        'submitted_by','submitted_at','reviewed_by','reviewed_at','review_note'];
    protected $casts = ['grade' => 'decimal:2','submitted_at' => 'datetime','reviewed_at' => 'datetime'];
    public function student() { return $this->belongsTo(Student::class); }
    public function offering() { return $this->belongsTo(SubjectOffering::class, 'subject_offering_id'); }
    public function submitter() { return $this->belongsTo(User::class, 'submitted_by'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
