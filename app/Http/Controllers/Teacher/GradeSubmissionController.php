<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\GradeRecord;
use App\Models\Student;
use App\Models\SchoolYear;
use App\Services\ActivityRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GradeSubmissionController extends Controller
{
    protected ActivityRecorder $activityRecorder;

    public function __construct(ActivityRecorder $activityRecorder)
    {
        $this->activityRecorder = $activityRecorder;
    }

    /**
     * Display grade submission progress.
     */
    public function index(Request $request)
    {
        // Get the active faculty profile of the logged-in user.
        $faculty = Faculty::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->firstOrFail();

        // Get the currently active school year.
        $activeSchoolYear = SchoolYear::query()
            ->where('status', 'active')
            ->firstOrFail();

        $schoolYear = $activeSchoolYear->year_name;

        // Prevent accessing grade submission for another school year.
        if (
            $request->filled('school_year') &&
            $request->query('school_year') !== $schoolYear
        ) {
            abort(404);
        }

        // Use the active school's current grading term by default.
        $term = (int) $request->query(
            'term',
            $activeSchoolYear->current_term ?? 1
        );

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
            ->where('school_year', $schoolYear)
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

            // Retrieve grade record statuses for this assignment and term.
            $submissionRecords = GradeRecord::query()
                ->where('teaching_assignment_id', $assignment->id)
                ->where('school_year', $schoolYear)
                ->where('term', $term)
                ->whereIn('student_id', $studentIds)
                ->get([
                    'status',
                    'adviser_return_reason',
                    'admin_return_reason',
                ]);

            $statusCounts = $submissionRecords->countBy('status');

            $submissionStatus = 'not_started';

            if ($submissionRecords->isNotEmpty()) {

                if ($submissionRecords->count() !== $assignmentTotal) {

                    $submissionStatus = 'incomplete_records';

                } elseif (
                    $submissionRecords->every(function ($record) {
                        return in_array($record->status, ['draft', 'returned'], true);
                    })
                ) {

                    // Allow mixed draft and returned records to be submitted together.
                    $submissionStatus = $submissionRecords->contains('status', 'returned')
                        ? 'returned'
                        : 'draft';

                } elseif ($statusCounts->count() === 1) {

                    $submissionStatus = $submissionRecords->first()->status;

                } else {

                    $submissionStatus = 'inconsistent';

                }
            }

            // Get the latest available return feedback.
            $returnReason = $submissionRecords
                ->pluck('admin_return_reason')
                ->filter()
                ->first();

            if (!$returnReason) {
                $returnReason = $submissionRecords
                    ->pluck('adviser_return_reason')
                    ->filter()
                    ->first();
            }

            // Attach submission status and feedback to the assignment.
            $assignment->setAttribute('submission_status', $submissionStatus);
            $assignment->setAttribute('return_reason', $returnReason);
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

            // Lock the active school year to prevent simultaneous
            // submission and school year activation conflicts.
            $activeSchoolYear = SchoolYear::query()
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (!$activeSchoolYear) {
                throw ValidationException::withMessages([
                    'school_year' => 'There is no active school year. Grade submission is unavailable.',
                ]);
            }

            // Only allow submissions for the active school year.
            if ($validated['school_year'] !== $activeSchoolYear->year_name) {
                throw ValidationException::withMessages([
                    'school_year' => 'You can only submit grades for the currently active school year.',
                ]);
            }

            // Make sure the assignment belongs to this faculty.
            $assignment = $faculty->teachingAssignments()
                ->whereKey($validated['assignment_id'])
                ->where('school_year', $activeSchoolYear->year_name)
                ->first();

            if (!$assignment) {
                throw ValidationException::withMessages([
                    'assignment_id' => 'The selected teaching assignment does not belong to your active school year.',
                ]);
            }

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

            // Capture the previous statuses for the audit log.
            $previousStatusCounts = $gradeRecords
                ->countBy('status')
                ->toArray();

            // All students passed validation.
            // Resubmit every grade record together.
            // Preserve assessment scores and computed grades.
            // Clear previous review feedback and timestamps.
            GradeRecord::query()
                ->whereIn('id', $gradeRecords->pluck('id'))
                ->update([
                    'status' => 'submitted',
                    'submitted_at' => now(),

                    // Clear previous Adviser review information.
                    'adviser_reviewed_at' => null,
                    'adviser_return_reason' => null,

                    // Clear previous Administrator review information.
                    'admin_reviewed_at' => null,
                    'admin_return_reason' => null,

                    'updated_at' => now(),
                ]);

            // Record the successful grade submission.
            // This is inside the transaction, so it will roll back
            // if the grade submission transaction fails.
            $this->activityRecorder->record(
                action: 'submit',
                type: 'grade_submission',
                key: $assignment->id . '|' .
                $validated['school_year'] . '|' .
                $validated['term'],
                before: [
                    'status_counts' => $previousStatusCounts,
                ],
                after: [
                    'status' => 'submitted',
                ],
                details: [
                    'teaching_assignment_id' => $assignment->id,
                    'grade_level' => $assignment->grade_level,
                    'subject' => $assignment->subject,
                    'school_year' => $validated['school_year'],
                    'term' => (int) $validated['term'],
                    'student_count' => $students->count(),
                    'description' => 'Teacher submitted grades to the Adviser for review.',
                ]
            );
        });

        return redirect()->back()->with(
            'success',
            'Grades successfully submitted to the Adviser for review.'
        );
    }
}
