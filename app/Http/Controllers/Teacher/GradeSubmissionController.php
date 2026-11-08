<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\GradeRecord;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GradeSubmissionController extends Controller
{
    /**
     * Display grade submission progress.
     */
    public function index(Request $request)
    {
        // Get the active faculty profile of the logged-in user.
        $faculty = Faculty::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->firstOrFail();

        // Current school year and selected term.
        $schoolYear = $request->query('school_year', '2026-2027');

        $term = (int) $request->query('term', 1);

        abort_unless(in_array($term, [1, 2, 3], true), 404);

        // Required assessments for a complete grade record.
        $requiredAssessmentCodes = [
            'WW1',
            'WW2',
            'WW3',
            'WW4',
            'PT1',
            'PT2',
            'SA',
            'SB',
            'SC',
            'TE',
        ];

        // Get only the teaching assignments of this faculty.
        $assignments = $faculty->teachingAssignments()
            ->orderBy('grade_level')
            ->orderBy('subject')
            ->get();

        $totalStudents = 0;
        $totalCompletedStudents = 0;

        foreach ($assignments as $assignment) {

            // Get active students under this assignment's grade level.
            $studentIds = Student::where('grade_level', $assignment->grade_level)
                ->where('school_year', $schoolYear)
                ->where('status', 'active')
                ->pluck('id');

            $assignmentTotal = $studentIds->count();

            // Count students with all 10 required assessment scores saved.
            $completedStudents = GradeRecord::query()
                ->where('teaching_assignment_id', $assignment->id)
                ->where('school_year', $schoolYear)
                ->where('term', $term)
                ->whereIn('student_id', $studentIds)
                ->withCount([
                    'assessmentScores as completed_assessments_count' => function ($query) use ($requiredAssessmentCodes) {
                        $query->whereIn('assessment_code', $requiredAssessmentCodes)
                            ->whereNotNull('score');
                    },
                ])
                ->get()
                ->where(
                    'completed_assessments_count',
                    count($requiredAssessmentCodes)
                )
                ->count();

            $incompleteStudents = $assignmentTotal - $completedStudents;

            $completionPercentage = $assignmentTotal > 0
                ? round(($completedStudents / $assignmentTotal) * 100, 2)
                : 0;

            // Attach completion information to this assignment.
            $assignment->setAttribute('total_students', $assignmentTotal);
            $assignment->setAttribute('completed_students', $completedStudents);
            $assignment->setAttribute('incomplete_students', $incompleteStudents);
            $assignment->setAttribute('completion_percentage', $completionPercentage);

            // Update overall summary totals.
            $totalStudents += $assignmentTotal;
            $totalCompletedStudents += $completedStudents;
        }

        $totalIncompleteStudents = $totalStudents - $totalCompletedStudents;

        $overallCompletionPercentage = $totalStudents > 0
            ? round(($totalCompletedStudents / $totalStudents) * 100, 2)
            : 0;

        $summary = [
            'assigned_classes' => $assignments->count(),
            'total_students' => $totalStudents,
            'completed_students' => $totalCompletedStudents,
            'incomplete_students' => $totalIncompleteStudents,
            'completion_percentage' => $overallCompletionPercentage,
        ];

        return view('teacher.grade-submission', compact(
            'faculty',
            'assignments',
            'schoolYear',
            'term',
            'summary'
        ));
    }

    /**
     * Submit all grades under one teaching assignment and term.
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'assignment_id' => ['required', 'integer'],
            'school_year' => ['required', 'string', 'max:20'],
            'term' => ['required', 'integer', 'in:1,2,3'],
        ]);

        

        $faculty = Faculty::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->firstOrFail();

        $requiredAssessmentCodes = [
            'WW1',
            'WW2',
            'WW3',
            'WW4',
            'PT1',
            'PT2',
            'SA',
            'SB',
            'SC',
            'TE',
        ];

        DB::transaction(function () use ($faculty, $validated, $requiredAssessmentCodes) {
            // Make sure the assignment belongs to this faculty.
            $assignment = $faculty->teachingAssignments()
                ->whereKey($validated['assignment_id'])
                ->firstOrFail();

            // Get all active students under this assignment.
            $students = Student::where('grade_level', $assignment->grade_level)
                ->where('school_year', $validated['school_year'])
                ->where('status', 'active')
                ->get();

            if ($students->isEmpty()) {
                throw ValidationException::withMessages([
                    'assignment_id' => 'There are no active students under this teaching assignment.',
                ]);
            }

            $studentIds = $students->pluck('id');

            // Retrieve all existing grade records for this assignment and term.
            $gradeRecords = GradeRecord::query()
                ->where('teaching_assignment_id', $assignment->id)
                ->where('school_year', $validated['school_year'])
                ->where('term', $validated['term'])
                ->whereIn('student_id', $studentIds)
                ->with('assessmentScores')
                ->lockForUpdate()
                ->get()
                ->keyBy('student_id');

            // Every active student must have a grade record.
            if ($gradeRecords->count() !== $students->count()) {
                $missingCount = $students->count() - $gradeRecords->count();

                throw ValidationException::withMessages([
                    'grades' => "{$missingCount} student(s) still have no grade record. Complete all student records before submitting.",
                ]);
            }

            foreach ($students as $student) {

                $gradeRecord = $gradeRecords->get($student->id);

                if (!$gradeRecord) {
                    throw ValidationException::withMessages([
                        'grades' => 'A student grade record is missing. Submission cancelled.',
                    ]);
                }

                // Only draft and returned records can be submitted.
                if (!in_array($gradeRecord->status, ['draft', 'returned'], true)) {
                    throw ValidationException::withMessages([
                        'grades' => 'Some grades have already been submitted or reviewed. Please refresh the page.',
                    ]);
                }

                $scores = $gradeRecord->assessmentScores->keyBy('assessment_code');

                // Validate all 10 required assessments.
                foreach ($requiredAssessmentCodes as $code) {

                    $assessment = $scores->get($code);

                    if (!$assessment || $assessment->score === null) {
                        throw ValidationException::withMessages([
                            'grades' => "Incomplete grades found. Assessment {$code} is missing for one or more students.",
                        ]);
                    }

                    $score = (float) $assessment->score;
                    $maximum = (float) $assessment->highest_possible_score;

                    if ($maximum <= 0) {
                        throw ValidationException::withMessages([
                            'grades' => "Invalid maximum score for assessment {$code}.",
                        ]);
                    }

                    if ($score < 0 || $score > $maximum) {
                        throw ValidationException::withMessages([
                            'grades' => "Invalid score found in assessment {$code}. Scores must be between 0 and {$maximum}.",
                        ]);
                    }
                }
            }

            // All students passed validation.
            // Update every grade record together.
            GradeRecord::query()
                ->whereIn('id', $gradeRecords->pluck('id'))
                ->update([
                    'status' => 'submitted',
                    'submitted_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        return redirect()->back()->with(
            'success',
            'Grades successfully submitted to the Adviser for review.'
        );
    }
}
