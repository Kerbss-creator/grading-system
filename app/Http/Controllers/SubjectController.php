<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\AdvisoryAssignment;
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

        // Default school year
        $schoolYear = $request->query('school_year', '2026-2027');

        $gradeLevels = [
            'Grade 7',
            'Grade 8',
            'Grade 9',
            'Grade 10',
        ];

        // Get subject teachers assigned to this subject
        $teachingAssignments = TeachingAssignment::with('faculty')
            ->where('subject', $subject->subject_name)
            ->whereIn('grade_level', $gradeLevels)
            ->get()
            ->keyBy('grade_level');

        // Get advisers assigned to each grade level
        // Include legacy assignments with NULL school_year.
        $advisoryAssignments = AdvisoryAssignment::with('faculty')
            ->whereIn('grade_level', $gradeLevels)
            ->where(function ($query) use ($schoolYear) {
                $query->where('school_year', $schoolYear)
                    ->orWhereNull('school_year');
            })
            ->get()
            ->groupBy('grade_level');

        // Prepare Grade 7–10 assignments
        $gradeAssignments = collect($gradeLevels)
            ->map(function ($gradeLevel) use ($teachingAssignments, $advisoryAssignments) {
                return [
                    'grade_level' => $gradeLevel,

                    'teacher' => $teachingAssignments
                        ->get($gradeLevel)?->faculty,

                    'advisers' => $advisoryAssignments
                        ->get($gradeLevel, collect())
                        ->pluck('faculty')
                        ->filter()
                        ->unique('id')
                        ->values(),
                ];
            });

        // Get unique assigned teachers
        $assignedTeachers = $gradeAssignments
            ->pluck('teacher')
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
