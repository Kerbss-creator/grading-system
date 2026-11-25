<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\AdvisoryAssignment;
use App\Models\SchoolYear;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Display all predefined subjects.
     */
    public function index()
    {
        $subjects = Subject::with('gradeLevels')
            ->orderBy('subject_name')
            ->get();

        return view('admin.subject-management', compact('subjects'));
    }

    /**
     * Display subject details and grade-level assignments.
     */
    public function show(Request $request)
    {
        $subject = Subject::with('gradeLevels')
            ->where('subject_code', $request->query('code'))
            ->firstOrFail();

        // Use selected school year or the active school year.
        $schoolYear = $request->query(
            'school_year',
            SchoolYear::active()?->year_name ?? '2026-2027'
        );

        $gradeLevels = [
            'Grade 7',
            'Grade 8',
            'Grade 9',
            'Grade 10',
        ];

        // Get all assignments for this subject and school year.
        // Do not keyBy grade_level because MAPEH has two components.
        $teachingAssignments = TeachingAssignment::with('faculty')
            ->where('subject', $subject->subject_name)
            ->whereIn('grade_level', $gradeLevels)
            ->where('school_year', $schoolYear)
            ->orderBy('grade_level')
            ->orderBy('component')
            ->get()
            ->groupBy('grade_level');

        // Get advisers assigned to the selected school year.
        $advisoryAssignments = AdvisoryAssignment::with('faculty')
            ->whereIn('grade_level', $gradeLevels)
            ->where(function ($query) use ($schoolYear) {
                $query->where('school_year', $schoolYear)
                    ->orWhereNull('school_year');
            })
            ->orderByRaw(
                'CASE WHEN school_year IS NULL THEN 1 ELSE 0 END'
            )
            ->get()
            ->groupBy('grade_level');

        // Prepare Grade 7–10 assignments.
        $gradeAssignments = collect($gradeLevels)
            ->map(function ($gradeLevel) use ($teachingAssignments, $advisoryAssignments) {
                $assignments = $teachingAssignments
                    ->get($gradeLevel, collect())
                    ->filter(function ($assignment) {
                        return $assignment->faculty !== null;
                    })
                    ->values();

                $advisers = $advisoryAssignments
                    ->get($gradeLevel, collect())
                    ->filter(function ($assignment) {
                        return $assignment->faculty !== null;
                    })
                    ->unique('faculty_id')
                    ->values();

                return [
                    'grade_level' => $gradeLevel,

                    // All component assignments for this grade.
                    'assignments' => $assignments,

                    // Backward-compatible property for existing Blade.
                    // For MAPEH, use assignments instead of this property.
                    'teacher' => $assignments->first()?->faculty,

                    // Adviser assigned to this grade level.
                    'advisers' => $advisers->pluck('faculty')
                        ->unique('id')
                        ->values(),
                ];
            });

        // Get unique assigned teachers across all grade levels/components.
        $assignedTeachers = $gradeAssignments
            ->flatMap(function ($grade) {
                return $grade['assignments']->pluck('faculty');
            })
            ->filter()
            ->unique('id')
            ->values();

        $assignedTeacherCount = $assignedTeachers->count();

        return view('admin.subject-details', compact(
            'subject',
            'schoolYear',
            'gradeAssignments',
            'assignedTeachers',
            'assignedTeacherCount'
        ));
    }
}
