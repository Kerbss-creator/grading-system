<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\Student;
use App\Models\SchoolYear;
use App\Models\Announcement;
use App\Models\AdvisoryAssignment;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please log in first.');
        }

        // Get the student linked to the authenticated user.
        $student = $user->student;

        // Get the active school year.
        $schoolYear = SchoolYear::active();

        // Get the latest announcements for students.
        $announcements = Announcement::whereIn('audience', ['all', 'students'])
            ->latest()
            ->take(5)
            ->get();

        // Find the assigned adviser using grade level and active school year.
        $adviserAssignment = null;

        if ($student && $schoolYear) {
            $adviserAssignment = AdvisoryAssignment::with('faculty')
                ->where('grade_level', $student->grade_level)
                ->where('school_year', $schoolYear->year_name)
                ->first();
        }

        $adviser = $adviserAssignment?->faculty;

        // ==========================================
        // SUBJECTS AND ASSIGNED SUBJECT TEACHERS
        // ==========================================

        $subjectTeachers = collect();

        if ($student && $schoolYear) {

            // Convert the student's grade level into a number.
            // Example: "Grade 7" becomes 7.
            $gradeNumber = (int) preg_replace(
                '/\D+/',
                '',
                $student->grade_level
            );

            $gradeLabel = 'Grade ' . $gradeNumber;

            // Get subjects offered for the student's grade level.
            $subjectTeachers = DB::table('grade_level_subjects')
                ->join(
                    'subjects',
                    'grade_level_subjects.subject_id',
                    '=',
                    'subjects.id'
                )
                ->where('grade_level_subjects.grade_level', $gradeNumber)
                ->where('subjects.status', 'active')
                ->select(
                    'subjects.id',
                    'subjects.subject_name',
                    'subjects.subject_code'
                )
                ->orderBy('subjects.subject_name')
                ->get();

            // Get the assigned teacher/s for each subject.
            foreach ($subjectTeachers as $subject) {

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
                        'faculty.first_name',
                        'faculty.middle_name',
                        'faculty.last_name',
                        'teaching_assignments.component'
                    )
                    ->orderBy('teaching_assignments.component')
                    ->get();
            }
        }

        // ==========================================
        // SCHOOL YEAR AND CURRENT TERM
        // ==========================================

        $schoolYearLabel = $schoolYear?->year_name ?? 'Not Set';

        $currentTermLabel = $schoolYear?->current_term_label ?? 'Not Set';

        return view('student.dashboard', compact(
            'student',
            'user',
            'schoolYear',
            'schoolYearLabel',
            'currentTermLabel',
            'announcements',
            'adviser',
            'subjectTeachers'
        ));
    }
}