<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ClassAdviser extends Model
{
    protected $fillable = ['grade_level','section','school_year','teacher_id'];
    public function teacher() { return $this->belongsTo(Teacher::class); }
    public function studentsQuery() { return Student::where('grade_level',$this->grade_level)->where('section',$this->section)->where('school_year',$this->school_year); }
}
