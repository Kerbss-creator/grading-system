<?php

namespace App\Http\Controllers\Teacher;

use function view;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        // Get the logged-in teacher's faculty record.
        $faculty = Faculty::where('user_id', auth()->id())
            ->where('status', 'active')
            ->firstOrFail();

        // Get all school years.
        $schoolYears = SchoolYear::orderByDesc('year_name')->get();

        // Get the currently active school year.
        $activeSchoolYear = SchoolYear::active();

        abort_unless($activeSchoolYear, 403, 'No active school year is configured.');

        // Selected school year; default to the active school year.
        $selectedSchoolYear = $request->query(
            'school_year',
            $activeSchoolYear->year_name
        );

        // Make sure the selected school year exists.
        $schoolYearRecord = SchoolYear::where(
            'year_name',
            $selectedSchoolYear
        )->firstOrFail();

        // Selected term; default to the selected year's current term.
        $selectedTerm = (int) $request->query(
            'term',
            $schoolYearRecord->current_term
        );

        abort_unless(in_array($selectedTerm, [1, 2, 3], true), 422);

        // Get only this teacher's assignments for the selected school year.
        $assignments = $faculty->teachingAssignments()
            ->where('school_year', $schoolYearRecord->year_name)
            ->get();

        // Attach the number of active students per grade level.
        $assignments->each(function ($assignment) use ($schoolYearRecord) {
            $assignment->student_count = Student::where(
                'grade_level',
                $assignment->grade_level
            )
                ->where('school_year', $schoolYearRecord->year_name)
                ->where('status', 'active')
                ->count();
        });

        return view('teacher.subjects', compact(
            'assignments',
            'schoolYears',
            'selectedSchoolYear',
            'selectedTerm'
        ));
    }
}