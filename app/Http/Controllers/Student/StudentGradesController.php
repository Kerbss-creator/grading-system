<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\GradePublication;
use App\Models\GradeRecord;
use App\Models\SchoolYear;
use App\Models\AdvisoryAssignment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class StudentGradesController extends Controller
{
    public function index()
    {
        // ==========================================
        // GET AUTHENTICATED STUDENT
        // ==========================================

        $student = Auth::user()?->student;

        if (!$student) {
            abort(403, 'Student account is not properly linked.');
        }

        // ==========================================
        // GET ACTIVE SCHOOL YEAR AND TERM
        // ==========================================

        $activeSchoolYear = SchoolYear::active();

        $selectedSchoolYear = $activeSchoolYear?->year_name;
        $selectedTerm = $activeSchoolYear?->current_term;

        // ==========================================
        // GET CLASS ADVISER
        // ==========================================

        $adviserAssignment = null;

        if ($selectedSchoolYear) {
            $adviserAssignment = AdvisoryAssignment::with('faculty')
                ->where('grade_level', $student->grade_level)
                ->where('school_year', $selectedSchoolYear)
                ->first();
        }

        $adviser = $adviserAssignment?->faculty;

        // ==========================================
        // GET ADMINISTRATOR
        // ==========================================

        $administrator = User::whereIn('role', [
            'admin',
            'administrator'
        ])
            ->where('account_status', 'active')
            ->orderBy('id')
            ->first();

        // ==========================================
        // INITIALIZE GRADES
        // ==========================================

        $grades = collect();

        // Check whether the active term has been published.
        $isPublished = false;

        if (
            $activeSchoolYear &&
            in_array($selectedTerm, [1, 2, 3], true)
        ) {
            $isPublished = GradePublication::where(
                'school_year',
                $selectedSchoolYear
            )
                ->where('term', $selectedTerm)
                ->exists();
        }

        // ==========================================
        // GET APPROVED GRADES
        // ONLY IF THE TERM IS PUBLISHED
        // ==========================================

        if ($isPublished) {
            $grades = GradeRecord::with('teachingAssignment')
                ->where('student_id', $student->id)
                ->where('school_year', $selectedSchoolYear)
                ->where('term', $selectedTerm)
                ->where('status', 'approved')
                ->get()
                ->sortBy(function ($record) {
                    $assignment = $record->teachingAssignment;

                    return ($assignment?->subject ?? '')
                        . ' '
                        . ($assignment?->component ?? '');
                })
                ->values();
        }

        // ==========================================
        // RETURN STUDENT GRADES PAGE
        // ==========================================

        return view('student.grades', compact(
            'student',
            'grades',
            'selectedSchoolYear',
            'selectedTerm',
            'isPublished',
            'adviser',
            'administrator'
        ));
    }
}
