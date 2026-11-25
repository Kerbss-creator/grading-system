<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class AdviserSubjectController extends Controller
{
    public function index(Request $request)
    {
        // Get the active school year.
        $activeSchoolYear = SchoolYear::active();

        abort_unless(
            $activeSchoolYear,
            403,
            'No active school year is configured.'
        );

        // Get the selected school year.
        $selectedSchoolYear = $request->query(
            'school_year',
            $activeSchoolYear->year_name
        );

        $schoolYearRecord = SchoolYear::where(
            'year_name',
            $selectedSchoolYear
        )->firstOrFail();

        // Get the selected term.
        $selectedTerm = (int) $request->query(
            'term',
            $schoolYearRecord->current_term
        );

        abort_unless(
            in_array($selectedTerm, [1, 2, 3], true),
            422
        );

        // Get the active faculty profile of the adviser.
        $faculty = Faculty::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->firstOrFail();

        // Get only the subjects assigned to this adviser.
        $assignments = $faculty->teachingAssignments()
            ->where('school_year', $schoolYearRecord->year_name)
            ->orderBy('grade_level')
            ->orderBy('subject')
            ->get();

        // Count active students for each assigned grade level.
        $assignments->each(function ($assignment) use ($schoolYearRecord) {
            $assignment->student_count = Student::where(
                'grade_level',
                $assignment->grade_level
            )
                ->where('school_year', $schoolYearRecord->year_name)
                ->where('status', 'active')
                ->count();
        });

        // Get available school years for the dropdown.
        $schoolYears = SchoolYear::orderByDesc('year_name')->get();

        return view('adviser.subjects', compact(
            'faculty',
            'assignments',
            'schoolYears',
            'selectedSchoolYear',
            'selectedTerm'
        ));
    }
}
