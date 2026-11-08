<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\AdvisoryAssignment;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class AdviserStudentController extends Controller
{
    public function index(Request $request)
    {
        $schoolYear = $request->query('school_year', '2026-2027');
        $term = (string) $request->query('term', 'overall');

        abort_unless(
            in_array($term, ['overall', '1', '2', '3'], true),
            404
        );

        // Get the logged-in adviser's faculty profile
        $faculty = Faculty::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->firstOrFail();

        // Get the adviser's grade-level assignment
        $advisoryAssignment = AdvisoryAssignment::where('faculty_id', $faculty->id)
            ->where(function ($query) use ($schoolYear) {
                $query->where('school_year', $schoolYear)
                    ->orWhereNull('school_year');
            })
            ->orderByRaw('school_year IS NULL ASC')
            ->first();

        abort_unless(
            $advisoryAssignment,
            403,
            'You have no advisory class assigned.'
        );

        $gradeLevel = $advisoryAssignment->grade_level;

        // Get students assigned to this grade level only
        $students = Student::where('grade_level', $gradeLevel)
            ->where('school_year', $schoolYear)
            ->where('status', 'active')
            ->with([
                'gradeRecords' => function ($query) use ($gradeLevel, $schoolYear) {
                    $query->where('school_year', $schoolYear)
                        ->whereIn('term', [1, 2, 3])
                        ->where('status', 'adviser_approved')
                        ->whereHas('teachingAssignment', function ($assignmentQuery) use ($gradeLevel) {
                            $assignmentQuery->where('grade_level', $gradeLevel);
                        })
                        ->with('teachingAssignment');
                },
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Get all subjects assigned to the adviser's grade level
        $subjects = TeachingAssignment::where('grade_level', $gradeLevel)
            ->select('subject')
            ->distinct()
            ->orderBy('subject')
            ->pluck('subject');

        $totalStudents = $students->count();

        return view('adviser.students', compact(
            'faculty',
            'advisoryAssignment',
            'gradeLevel',
            'schoolYear',
            'term',
            'students',
            'subjects',
            'totalStudents'
        ));
    }
}
