<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SchoolYear;
use App\Models\AdvisoryAssignment;

class StudentSubjectsController extends Controller
{
    public function index()
    {
        // ==========================================
        // GET AUTHENTICATED USER AND STUDENT
        // ==========================================

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please log in first.');
        }

        // Get the student linked to the authenticated account.
        $student = $user->student;

        // ==========================================
        // GET ACTIVE SCHOOL YEAR
        // ==========================================

        $schoolYear = SchoolYear::active();

        // ==========================================
        // GET ASSIGNED CLASS ADVISER
        // ==========================================

        $adviserAssignment = null;

        if ($student && $schoolYear) {
            $adviserAssignment = AdvisoryAssignment::with('faculty')
                ->where('grade_level', $student->grade_level)
                ->where('school_year', $schoolYear->year_name)
                ->first();
        }

        $adviser = $adviserAssignment?->faculty;

        // ==========================================
        // INITIALIZE SUBJECTS
        // ==========================================

        $subjects = collect();

        // Get subjects only when student and active school year exist.
        if ($student && $schoolYear) {

            // Convert grade level into a number.
            // Example: "Grade 9" becomes 9.
            $gradeNumber = (int) preg_replace(
                '/\D+/',
                '',
                (string) $student->grade_level
            );

            // Convert it to the teaching assignment format.
            // Example: 9 becomes "Grade 9".
            $gradeLabel = 'Grade ' . $gradeNumber;

            if ($gradeNumber >= 7 && $gradeNumber <= 10) {

                // ==========================================
                // GET SUBJECTS FOR THE STUDENT'S GRADE LEVEL
                // ==========================================

                $subjects = DB::table('grade_level_subjects')
                    ->join(
                        'subjects',
                        'grade_level_subjects.subject_id',
                        '=',
                        'subjects.id'
                    )
                    ->where(
                        'grade_level_subjects.grade_level',
                        $gradeNumber
                    )
                    ->where('subjects.status', 'active')
                    ->select(
                        'subjects.id',
                        'subjects.subject_name',
                        'subjects.subject_code',
                        'subjects.subject_type'
                    )
                    ->orderBy('subjects.subject_name')
                    ->get();

                // ==========================================
                // GET ASSIGNED TEACHERS FOR EACH SUBJECT
                // ==========================================

                foreach ($subjects as $subject) {

                    $subject->teachers = DB::table('teaching_assignments')
                        ->join(
                            'faculty',
                            'teaching_assignments.faculty_id',
                            '=',
                            'faculty.id'
                        )
                        ->where(
                            'teaching_assignments.grade_level',
                            $gradeLabel
                        )
                        ->where(
                            'teaching_assignments.school_year',
                            $schoolYear->year_name
                        )
                        ->where(
                            'teaching_assignments.subject',
                            $subject->subject_name
                        )
                        ->where('faculty.status', 'active')
                        ->whereNull('faculty.deleted_at')
                        ->select(
                            'faculty.id',
                            'faculty.first_name',
                            'faculty.middle_name',
                            'faculty.last_name',
                            'teaching_assignments.component'
                        )
                        ->orderBy('teaching_assignments.component')
                        ->get();
                }
            }
        }

        // ==========================================
        // DYNAMIC SCHOOL YEAR LABEL
        // ==========================================

        $schoolYearLabel = $schoolYear?->year_name ?? 'Not Set';

        // ==========================================
        // RETURN MY SUBJECTS PAGE
        // ==========================================

        return view('student.subjects', compact(
            'user',
            'student',
            'schoolYear',
            'schoolYearLabel',
            'subjects',
            'adviser'
        ));
    }
}
