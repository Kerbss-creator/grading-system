<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\AdvisoryAssignment;
use App\Models\TeachingAssignment;
use App\Models\GradeRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdviserGradeReviewController extends Controller
{
    /**
     * Get the active faculty and advisory grade levels
     * assigned to the logged-in adviser.
     */
    private function getAdviserContext(string $schoolYear): array
    {
        $faculty = Faculty::where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        abort_unless($faculty, 403, 'Faculty record not found.');

        $advisoryAssignments = AdvisoryAssignment::where(
            'faculty_id',
            $faculty->id
        )
            ->where(function ($query) use ($schoolYear) {
                $query->where('school_year', $schoolYear)
                    ->orWhereNull('school_year');
            })
            ->get();

        $assignedGradeLevels = $advisoryAssignments
            ->pluck('grade_level')
            ->unique()
            ->values();

        return [
            'faculty' => $faculty,
            'assignedGradeLevels' => $assignedGradeLevels,
        ];
    }

    /**
     * Validate that the teaching assignment belongs
     * to the adviser's assigned grade level.
     */
    private function getAuthorizedAssignment(
        int $assignmentId,
        string $schoolYear
    ): TeachingAssignment {
        $context = $this->getAdviserContext($schoolYear);

        abort_unless(
            $context['assignedGradeLevels']->isNotEmpty(),
            403,
            'You have no advisory assignment for this school year.'
        );

        return TeachingAssignment::whereKey($assignmentId)
            ->whereIn(
                'grade_level',
                $context['assignedGradeLevels']
            )
            ->firstOrFail();
    }

    /**
     * Display pending grade submissions and review history.
     */
    public function index(Request $request)
    {
        $schoolYear = '2026-2027';

        $context = $this->getAdviserContext($schoolYear);

        $faculty = $context['faculty'];
        $assignedGradeLevels = $context['assignedGradeLevels'];

        $pendingReviews = collect();
        $reviewHistory = collect();

        if ($assignedGradeLevels->isNotEmpty()) {

            $records = GradeRecord::with([
                'student',
                'teachingAssignment.faculty',
            ])
                ->where('school_year', $schoolYear)
                ->whereIn('status', [
                    'submitted',
                    'forwarded_to_admin',
                    'approved',
                    'returned',
                ])
                ->whereHas('teachingAssignment', function ($query) use ($assignedGradeLevels) {
                    $query->whereIn(
                        'grade_level',
                        $assignedGradeLevels
                    );
                })
                ->get();

            // Group records into one batch per assignment,
            // school year, and term.
            $submissions = $records
                ->groupBy(function ($record) {
                    return $record->teaching_assignment_id
                        . '|' . $record->school_year
                        . '|' . $record->term;
                })
                ->map(function ($group) {

                    $latestRecord = $group
                        ->sortByDesc('submitted_at')
                        ->first();

                    $statuses = $group
                        ->pluck('status')
                        ->unique()
                        ->values();

                    // A batch must have one consistent status.
                    $batchStatus = $statuses->count() === 1
                        ? $statuses->first()
                        : 'inconsistent';

                    return (object) [
                        'teaching_assignment_id' =>
                            $latestRecord->teaching_assignment_id,

                        'teachingAssignment' =>
                            $latestRecord->teachingAssignment,

                        'faculty' =>
                            $latestRecord->teachingAssignment?->faculty,

                        'school_year' =>
                            $latestRecord->school_year,

                        'term' =>
                            $latestRecord->term,

                        'grade_level' =>
                            $latestRecord->teachingAssignment?->grade_level,

                        'submitted_at' =>
                            $group->max('submitted_at'),

                        'student_count' =>
                            $group->count(),

                        'status' => $batchStatus,
                    ];
                })
                ->values();

            $pendingReviews = $submissions
                ->where('status', 'submitted')
                ->sortByDesc('submitted_at')
                ->values();

            $reviewHistory = $submissions
                ->whereIn('status', [
                    'forwarded_to_admin',
                    'approved',
                    'returned',
                    'inconsistent',
                ])
                ->sortByDesc('submitted_at')
                ->values();
        }

        $pendingCount = $pendingReviews->count();

        $forwardedCount = $reviewHistory
            ->where('status', 'forwarded_to_admin')
            ->count();

        $approvedCount = $reviewHistory
            ->where('status', 'approved')
            ->count();

        $returnedCount = $reviewHistory
            ->where('status', 'returned')
            ->count();

        return view('adviser.grade-review', compact(
            'faculty',
            'schoolYear',
            'assignedGradeLevels',
            'pendingReviews',
            'reviewHistory',
            'pendingCount',
            'forwardedCount',
            'approvedCount',
            'returnedCount'
        ));
    }

    /**
     * Display the detailed student grades for one batch.
     */
    public function show(
        Request $request,
        int $assignmentId,
        string $schoolYear,
        int $term
    ) {
        abort_unless(in_array($term, [1, 2, 3], true), 404);

        $faculty = $this->getAdviserContext($schoolYear)['faculty'];

        $assignment = $this->getAuthorizedAssignment(
            $assignmentId,
            $schoolYear
        );

        $gradeRecords = GradeRecord::with([
            'student',
            'assessmentScores',
        ])
            ->where('teaching_assignment_id', $assignment->id)
            ->where('school_year', $schoolYear)
            ->where('term', $term)
            ->orderBy(
                'student_id'
            )
            ->get();

        abort_if(
            $gradeRecords->isEmpty(),
            404,
            'No grade records found for this submission.'
        );

        $statuses = $gradeRecords
            ->pluck('status')
            ->unique()
            ->values();

        abort_unless(
            $statuses->count() === 1,
            409,
            'This submission has inconsistent student record statuses. Please contact the administrator.'
        );

        $batchStatus = $statuses->first();

        abort_unless(
            in_array($batchStatus, [
                'submitted',
                'forwarded_to_admin',
                'approved',
                'returned',
            ], true),
            404
        );

        $gradeRecords = $gradeRecords
            ->sortBy(function ($record) {
                return $record->student?->last_name ?? '';
            })
            ->values();

        return view('adviser.grade-review-details', compact(
            'faculty',
            'assignment',
            'schoolYear',
            'term',
            'gradeRecords',
            'batchStatus'
        ));
    }

    /**
     * Forward the entire grade submission batch to Admin.
     */
    public function forward(
        Request $request,
        int $assignmentId,
        string $schoolYear,
        int $term
    ) {
        abort_unless(in_array($term, [1, 2, 3], true), 404);

        DB::transaction(function () use ($assignmentId, $schoolYear, $term) {
            $assignment = $this->getAuthorizedAssignment(
                $assignmentId,
                $schoolYear
            );

            $gradeRecords = GradeRecord::where(
                'teaching_assignment_id',
                $assignment->id
            )
                ->where('school_year', $schoolYear)
                ->where('term', $term)
                ->lockForUpdate()
                ->get();

            abort_if(
                $gradeRecords->isEmpty(),
                404,
                'No grade records found.'
            );

            $statuses = $gradeRecords
                ->pluck('status')
                ->unique();

            abort_unless(
                $statuses->count() === 1
                && $statuses->first() === 'submitted',
                409,
                'This batch is no longer awaiting adviser review.'
            );

            GradeRecord::whereIn(
                'id',
                $gradeRecords->pluck('id')
            )->update([
                        'status' => 'forwarded_to_admin',
                        'adviser_reviewed_at' => now(),
                        'updated_at' => now(),
                    ]);
        });

        return redirect()
            ->route('adviser.grade-review')
            ->with(
                'success',
                'Grade submission successfully forwarded to the Administrator.'
            );
    }

    /**
     * Return the entire grade submission batch to the Teacher.
     */
    public function returnToTeacher(
        Request $request,
        int $assignmentId,
        string $schoolYear,
        int $term
    ) {
        abort_unless(in_array($term, [1, 2, 3], true), 404);

        DB::transaction(function () use ($assignmentId, $schoolYear, $term) {
            $assignment = $this->getAuthorizedAssignment(
                $assignmentId,
                $schoolYear
            );

            $gradeRecords = GradeRecord::where(
                'teaching_assignment_id',
                $assignment->id
            )
                ->where('school_year', $schoolYear)
                ->where('term', $term)
                ->lockForUpdate()
                ->get();

            abort_if(
                $gradeRecords->isEmpty(),
                404,
                'No grade records found.'
            );

            $statuses = $gradeRecords
                ->pluck('status')
                ->unique();

            abort_unless(
                $statuses->count() === 1
                && $statuses->first() === 'submitted',
                409,
                'This batch is no longer awaiting adviser review.'
            );

            GradeRecord::whereIn(
                'id',
                $gradeRecords->pluck('id')
            )->update([
                        'status' => 'returned',
                        'adviser_reviewed_at' => now(),
                        'updated_at' => now(),
                    ]);
        });

        return redirect()
            ->route('adviser.grade-review')
            ->with(
                'success',
                'Grade submission successfully returned to the Teacher.'
            );
    }
}
