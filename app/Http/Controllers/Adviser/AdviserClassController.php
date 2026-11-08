<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\TeachingAssignment;
use App\Models\GradeRecord;
use App\Models\AdvisoryAssignment;
use Illuminate\Http\Request;

class AdviserClassController extends Controller
{
    public function index(Request $request)
    {
        // Current academic period
        $schoolYear = $request->query('school_year', '2026-2027');
        $term = (string) $request->query('term', 'overall');

        abort_unless(
            in_array($term, ['overall', '1', '2', '3'], true),
            404
        );

        // Get the active faculty profile
        $faculty = Faculty::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->firstOrFail();

        // Find the adviser's assigned grade level.
        // Prefer the current school year assignment.
        // Allow NULL school_year for older assignments.
        $advisoryAssignment = AdvisoryAssignment::where('faculty_id', $faculty->id)
            ->where(function ($query) use ($schoolYear) {
                $query->where('school_year', $schoolYear)
                    ->orWhereNull('school_year');
            })
            ->orderByRaw('school_year IS NULL ASC')
            ->first();

        abort_unless($advisoryAssignment, 403, 'You have no advisory class assigned.');

        $gradeLevel = $advisoryAssignment->grade_level;

        // Get active students under the assigned grade level
        $students = Student::where('grade_level', $gradeLevel)
            ->where('school_year', $schoolYear)
            ->where('status', 'active')
            ->with([
                'gradeRecords' => function ($query) use ($gradeLevel, $schoolYear) {
                    $query->where('school_year', $schoolYear)
                        ->whereIn('term', [1, 2, 3])
                        ->whereHas('teachingAssignment', function ($assignmentQuery) use ($gradeLevel) {
                            $assignmentQuery->where('grade_level', $gradeLevel);
                        })
                        ->with('teachingAssignment:id,faculty_id,grade_level,subject');
                },
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Get all subjects assigned to this grade level
        $subjects = TeachingAssignment::where('grade_level', $gradeLevel)
            ->select('subject')
            ->distinct()
            ->orderBy('subject')
            ->pluck('subject');

        // Base query: submissions from OTHER teachers
        // assigned to the adviser's grade level.
        $gradeReviewQuery = GradeRecord::where('school_year', $schoolYear)
            ->whereHas('teachingAssignment', function ($query) use ($gradeLevel, $faculty) {
                $query->where('grade_level', $gradeLevel)
                    ->where('faculty_id', '!=', $faculty->id);
            });

        // Count submissions waiting for adviser review
        $pendingReviews = (clone $gradeReviewQuery)
            ->where('status', 'submitted')
            ->count();

        // Count records already reviewed by the adviser
        $reviewedRecords = (clone $gradeReviewQuery)
            ->whereNotNull('adviser_reviewed_at')
            ->count();

        // Recent pending and reviewed submissions
        $recentGradeReviews = (clone $gradeReviewQuery)
            ->where(function ($query) {
                $query->where('status', 'submitted')
                    ->orWhereNotNull('adviser_reviewed_at');
            })
            ->with([
                'student',
                'teachingAssignment.faculty',
            ])
            ->orderByDesc('submitted_at')
            ->limit(5)
            ->get();

        $totalStudents = $students->count();

        return view('adviser.classes', compact(
            'faculty',
            'advisoryAssignment',
            'gradeLevel',
            'schoolYear',
            'term',
            'students',
            'subjects',
            'totalStudents',
            'pendingReviews',
            'reviewedRecords',
            'recentGradeReviews'
        ));
    }
}
