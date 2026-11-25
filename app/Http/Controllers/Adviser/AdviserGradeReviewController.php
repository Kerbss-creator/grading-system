<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\AdvisoryAssignment;
use App\Models\TeachingAssignment;
use App\Models\GradeRecord;
use App\Models\SchoolYear;
use App\Services\ActivityRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdviserGradeReviewController extends Controller
{
    protected ActivityRecorder $activityRecorder;

    public function __construct(ActivityRecorder $activityRecorder)
    {
        $this->activityRecorder = $activityRecorder;
    }

    /**
     * Get the currently active school year.
     */
    private function getActiveSchoolYear(): SchoolYear
    {
        return SchoolYear::query()
            ->where('status', 'active')
            ->firstOrFail();
    }

    /**
     * Get the active faculty and advisory grade levels
     * assigned to the logged-in adviser.
     */
    private function getAdviserContext(string $schoolYear): array
    {
        $activeSchoolYear = $this->getActiveSchoolYear();

        abort_unless(
            $activeSchoolYear->year_name === $schoolYear,
            404,
            'You can only access the active school year.'
        );

        $faculty = Faculty::where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        abort_unless($faculty, 403, 'Faculty record not found.');

        $advisoryAssignments = AdvisoryAssignment::where(
            'faculty_id',
            $faculty->id
        )
            ->where('school_year', $schoolYear)
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
     * to the adviser's assigned grade level and school year.
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

        return TeachingAssignment::query()
            ->whereKey($assignmentId)
            ->where('school_year', $schoolYear)
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
        $activeSchoolYear = $this->getActiveSchoolYear();

        $schoolYear = $activeSchoolYear->year_name;

        // Prevent accessing another school year's review records.
        if (
            $request->filled('school_year') &&
            $request->query('school_year') !== $schoolYear
        ) {
            abort(404);
        }

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
                ->whereHas('teachingAssignment', function ($query) use ($assignedGradeLevels, $schoolYear) {
                    $query->where('school_year', $schoolYear)
                        ->whereIn(
                            'grade_level',
                            $assignedGradeLevels
                        );
                })
                ->get();

            /**
             * Separate records by assignment, school year,
             * term, and status.
             */
            $submissions = $records
                ->groupBy(function ($record) {
                    return $record->teaching_assignment_id
                        . '|' . $record->school_year
                        . '|' . $record->term
                        . '|' . $record->status;
                })
                ->map(function ($group) {

                    $latestRecord = $group
                        ->sortByDesc('submitted_at')
                        ->first();

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

                        'status' =>
                            $latestRecord->status,
                    ];
                })
                ->values();

            // Only currently submitted records appear here.
            $pendingReviews = $submissions
                ->where('status', 'submitted')
                ->sortByDesc('submitted_at')
                ->values();

            // Previously reviewed records remain in history.
            $reviewHistory = $submissions
                ->whereIn('status', [
                    'forwarded_to_admin',
                    'approved',
                    'returned',
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
     * Display only currently submitted student grades.
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
            ->where('status', 'submitted')
            ->orderBy('student_id')
            ->get();

        abort_if(
            $gradeRecords->isEmpty(),
            404,
            'No pending submitted grade records found.'
        );

        $batchStatus = 'submitted';

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
     * Forward only currently submitted records to Admin.
     */
    public function forward(
        Request $request,
        int $assignmentId,
        string $schoolYear,
        int $term
    ) {
        abort_unless(in_array($term, [1, 2, 3], true), 404);

        DB::transaction(function () use ($assignmentId, $schoolYear, $term) {

            // Lock the active school year before forwarding.
            $activeSchoolYear = SchoolYear::query()
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (
                !$activeSchoolYear ||
                $activeSchoolYear->year_name !== $schoolYear
            ) {
                abort(409, 'The selected school year is no longer active.');
            }

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
                ->where('status', 'submitted')
                ->lockForUpdate()
                ->get();

            abort_if(
                $gradeRecords->isEmpty(),
                409,
                'There are no pending submitted grades to forward.'
            );

            GradeRecord::whereIn(
                'id',
                $gradeRecords->pluck('id')
            )->update([
                        'status' => 'forwarded_to_admin',
                        'adviser_reviewed_at' => now(),
                        'updated_at' => now(),
                    ]);

            // Audit log: Adviser forwarded grades to Administrator.
            $this->activityRecorder->record(
                action: 'forward',
                type: 'grade_review',
                key: $assignment->id . '|' .
                $schoolYear . '|' .
                $term,
                before: [
                    'status' => 'submitted',
                ],
                after: [
                    'status' => 'forwarded_to_admin',
                ],
                details: [
                    'teaching_assignment_id' => $assignment->id,
                    'grade_level' => $assignment->grade_level,
                    'subject' => $assignment->subject,
                    'school_year' => $schoolYear,
                    'term' => $term,
                    'student_count' => $gradeRecords->count(),
                    'description' => 'Adviser forwarded submitted grades to the Administrator for approval.',
                ]
            );
        });

        return redirect()
            ->route('adviser.grade-review')
            ->with(
                'success',
                'Submitted student grades successfully forwarded to the Administrator.'
            );
    }

    /**
     * Return only currently submitted records to the Teacher.
     */
    public function returnToTeacher(
        Request $request,
        int $assignmentId,
        string $schoolYear,
        int $term
    ) {
        abort_unless(in_array($term, [1, 2, 3], true), 404);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $reason = trim($validated['reason']);

        abort_if(
            $reason === '',
            422,
            'Please provide a valid reason for returning the grades.'
        );

        DB::transaction(function () use ($assignmentId, $schoolYear, $term, $reason) {

            // Lock the active school year before returning grades.
            $activeSchoolYear = SchoolYear::query()
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (
                !$activeSchoolYear ||
                $activeSchoolYear->year_name !== $schoolYear
            ) {
                abort(409, 'The selected school year is no longer active.');
            }

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
                ->where('status', 'submitted')
                ->lockForUpdate()
                ->get();

            abort_if(
                $gradeRecords->isEmpty(),
                409,
                'There are no pending submitted grades to return.'
            );

            GradeRecord::whereIn(
                'id',
                $gradeRecords->pluck('id')
            )->update([
                        'status' => 'returned',
                        'adviser_reviewed_at' => now(),
                        'adviser_return_reason' => $reason,
                        'admin_return_reason' => null,
                        'updated_at' => now(),
                    ]);

            // Audit log: Adviser returned grades to Teacher.
            $this->activityRecorder->record(
                action: 'return',
                type: 'grade_review',
                key: $assignment->id . '|' .
                $schoolYear . '|' .
                $term,
                before: [
                    'status' => 'submitted',
                ],
                after: [
                    'status' => 'returned',
                ],
                details: [
                    'teaching_assignment_id' => $assignment->id,
                    'grade_level' => $assignment->grade_level,
                    'subject' => $assignment->subject,
                    'school_year' => $schoolYear,
                    'term' => $term,
                    'student_count' => $gradeRecords->count(),
                    'return_reason' => $reason,
                    'description' => 'Adviser returned submitted grades to the Teacher for correction.',
                ]
            );
        });

        return redirect()
            ->route('adviser.grade-review')
            ->with(
                'success',
                'Submitted student grades successfully returned to the Teacher with your feedback.'
            );
    }
}
