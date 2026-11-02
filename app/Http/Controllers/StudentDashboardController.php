<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $student = Student::where('user_id', $user->id)->first()
            ?? Student::where('email', $user->email)->first();

        if (!$student) {
            $student = (object) [
                'student_id' => 'PENDING',
                'first_name' => explode(' ', $user->name)[0],
                'last_name' => explode(' ', $user->name)[1] ?? '',
                'grade_level' => 'N/A',
                'section' => 'N/A',
                'school_year' => '2026 - 2027'
            ];
        }

        $approvedGrades = collect();
        $mySubjects = collect();
        $adviser = null;
        if ($student instanceof Student) {
            $adviser = \App\Models\ClassAdviser::with('teacher')
                ->where('grade_level',$student->grade_level)->where('section',$student->section)
                ->where('school_year',$student->school_year)->first();
            $mySubjects = \App\Models\SubjectOffering::with(['subject', 'teacher'])
                ->whereHas('subject', fn ($q) => $q->where('grade_level', $student->grade_level)
                    ->where('school_year', $student->school_year)->where('is_active', true))
                ->where(fn ($q) => $q->where('section', 'ALL')->orWhere('section', $student->section ?: '__none__'))->get();
            $approvedGrades = \App\Models\GradeRecord::with('offering.subject')
                ->where('student_id', $student->id)->where('status', 'approved')->get();
        }
        return view('student.dashboard', compact('student', 'user', 'approvedGrades', 'mySubjects', 'adviser')); 
    }
}
