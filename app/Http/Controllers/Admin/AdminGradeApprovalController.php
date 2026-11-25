<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradeRecord;
use App\Models\GradePublication;
use App\Models\SchoolYear;
use App\Models\TeachingAssignment;
use App\Services\ActivityRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminGradeApprovalController extends Controller
{
    /**
     * Display grade submissions for Admin approval.
     */
    public function index(Request $request)
    {
        // Get available school years.
        $schoolYears = DB::table('school_years')
            ->orderByDesc('id')
            ->pluck('year_name');

        // Identify the active school year.
        $activeSchoolYear = DB::table('school_years')
            ->where('status', 'active')
            ->orderByDesc('id')
            ->first();

        // Use the active school year as the default.
        $defaultSchoolYear = $activeSchoolYear->year_name
            ?? $schoolYears->first();

        // Validate requested school year.
        $requestedSchoolYear = $request->query('school_year');

        $schoolYear = $requestedSchoolYear
            && $schoolYears->contains($requestedSchoolYear)
            ? $requestedSchoolYear
            : $defaultSchoolYear;

        // Retrieve records included in Admin Grade Approval.
        $records = GradeRecord::with([
            'student',
            'teachingAssignment.faculty',
        ])
            ->when($schoolYear, function ($query) use ($schoolYear) {
                $query->where('school_year', $schoolYear);
            })
            ->where(function ($query) {
                $query->whereIn('status', [
                    'forwarded_to_admin',
                    'approved',
                ])->orWhere(function ($query) {
                    $query->where('status', 'returned')
                        ->whereNotNull('admin_return_reason');
                });
            })
            ->get();

        // Group records by assignment, school year, term, and status.
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

                $assignment = $latestRecord->teachingAssignment;
                $faculty = $assignment?->faculty;

                $publication = GradePublication::where(
                    'school_year',
                    $latestRecord->school_year
                )
                    ->where('term', $latestRecord->term)
                    ->first();

                return (object) [
                    'teaching_assignment_id' =>
                        $latestRecord->teaching_assignment_id,

                    'teachingAssignment' => $assignment,

                    'grade_level' => $assignment?->grade_level,

                    'subject' => $assignment?->subject,

                    'teacher_name' => $faculty
                        ? trim(
                            $faculty->first_name . ' ' .
                            ($faculty->middle_name
                                ? $faculty->middle_name . ' '
                                : '') .
                            $faculty->last_name
                        )
                        : 'Unknown Faculty',

                    'school_year' => $latestRecord->school_year,

                    'term' => $latestRecord->term,

                    'submitted_at' => $group
                        ->pluck('submitted_at')
                        ->filter()
                        ->sortDesc()
                        ->first(),

                    'student_count' => $group->count(),

                    'publication_status' => $publication
                        ? 'published'
                        : 'not_published',

                    'published_at' => $publication?->published_at,

                    'status' => $latestRecord->status,

                    'return_reason' => $group
                        ->pluck('admin_return_reason')
                        ->filter()
                        ->first(),
                ];
            })
            ->sortByDesc('submitted_at')
            ->values();

        // Summary counts.
        $pendingCount = $submissions
            ->where('status', 'forwarded_to_admin')
            ->count();

        $approvedCount = $submissions
            ->where('status', 'approved')
            ->count();

        $returnedCount = $submissions
            ->where('status', 'returned')
            ->count();

        return view('admin.grade-approval', compact(
            'schoolYear',
            'schoolYears',
            'activeSchoolYear',
            'submissions',
            'pendingCount',
            'approvedCount',
            'returnedCount'
        ));
    }

    /**
     * Approve only records forwarded by the Adviser.
     */
    public function approve(
        int $assignmentId,
        string $schoolYear,
        int $term,
        ActivityRecorder $activityRecorder
    ) {
        abort_unless(in_array($term, [1, 2, 3], true), 404);

        DB::transaction(function () use ($assignmentId, $schoolYear, $term, $activityRecorder) {

            // Lock the active school year before approval.
            $activeSchoolYear = SchoolYear::query()
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (
                !$activeSchoolYear ||
                $activeSchoolYear->year_name !== $schoolYear
            ) {
                abort(
                    409,
                    'The selected school year is no longer active. Refresh the page.'
                );
            }

            // Verify that the teaching assignment belongs to this year.
            $assignment = TeachingAssignment::query()
                ->whereKey($assignmentId)
                ->where('school_year', $schoolYear)
                ->first();

            abort_unless(
                $assignment,
                404,
                'The selected teaching assignment was not found for this school year.'
            );

            // Lock only records awaiting Admin approval.
            $gradeRecords = GradeRecord::where(
                'teaching_assignment_id',
                $assignment->id
            )
                ->where('school_year', $schoolYear)
                ->where('term', $term)
                ->where('status', 'forwarded_to_admin')
                ->lockForUpdate()
                ->get();

            abort_if(
                $gradeRecords->isEmpty(),
                409,
                'No pending grade records are awaiting Admin approval.'
            );

            // Approve the entire submission batch.
            GradeRecord::whereIn(
                'id',
                $gradeRecords->pluck('id')
            )->update([
                        'status' => 'approved',
                        'admin_return_reason' => null,
                        'admin_reviewed_at' => now(),
                        'updated_at' => now(),
                    ]);

            // Record the activity in the audit log.
            $activityRecorder->record(
                action: 'approve',
                type: 'grade_submission',
                key: $assignmentId . '|' . $schoolYear . '|' . $term,
                before: [
                    'status' => 'forwarded_to_admin',
                ],
                after: [
                    'status' => 'approved',
                ],
                details: [
                    'description' => 'Administrator approved forwarded grade records.',
                    'teaching_assignment_id' => $assignmentId,
                    'grade_level' => $assignment->grade_level,
                    'subject' => $assignment->subject,
                    'school_year' => $schoolYear,
                    'term' => $term,
                    'student_count' => $gradeRecords->count(),
                ]
            );
        });

        return redirect()
            ->route('admin.grade-approval', [
                'school_year' => $schoolYear,
            ])
            ->with(
                'success',
                'Grade submission successfully approved.'
            );
    }

    /**
     * Return only records forwarded by the Adviser.
     */
    public function returnToTeacher(
        Request $request,
        int $assignmentId,
        string $schoolYear,
        int $term,
        ActivityRecorder $activityRecorder
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

        DB::transaction(function () use ($assignmentId, $schoolYear, $term, $reason, $activityRecorder) {

            // Lock the active school year before returning grades.
            $activeSchoolYear = SchoolYear::query()
                ->where('status', 'active')
                ->lockForUpdate()
                ->first();

            if (
                !$activeSchoolYear ||
                $activeSchoolYear->year_name !== $schoolYear
            ) {
                abort(
                    409,
                    'The selected school year is no longer active. Refresh the page.'
                );
            }

            // Verify that the teaching assignment belongs to this year.
            $assignment = TeachingAssignment::query()
                ->whereKey($assignmentId)
                ->where('school_year', $schoolYear)
                ->first();

            abort_unless(
                $assignment,
                404,
                'The selected teaching assignment was not found for this school year.'
            );

            // Lock only records awaiting Admin review.
            $gradeRecords = GradeRecord::where(
                'teaching_assignment_id',
                $assignment->id
            )
                ->where('school_year', $schoolYear)
                ->where('term', $term)
                ->where('status', 'forwarded_to_admin')
                ->lockForUpdate()
                ->get();

            abort_if(
                $gradeRecords->isEmpty(),
                409,
                'No pending grade records are awaiting Admin review.'
            );

            // Return the entire submission batch to the Teacher.
            GradeRecord::whereIn(
                'id',
                $gradeRecords->pluck('id')
            )->update([
                        'status' => 'returned',
                        'admin_return_reason' => $reason,
                        'admin_reviewed_at' => now(),
                        'updated_at' => now(),
                    ]);

            // Record the activity in the audit log.
            $activityRecorder->record(
                action: 'return',
                type: 'grade_submission',
                key: $assignmentId . '|' . $schoolYear . '|' . $term,
                before: [
                    'status' => 'forwarded_to_admin',
                ],
                after: [
                    'status' => 'returned',
                    'return_reason' => $reason,
                ],
                details: [
                    'description' => 'Administrator returned forwarded grade records to the Teacher.',
                    'teaching_assignment_id' => $assignmentId,
                    'grade_level' => $assignment->grade_level,
                    'subject' => $assignment->subject,
                    'school_year' => $schoolYear,
                    'term' => $term,
                    'student_count' => $gradeRecords->count(),
                    'return_reason' => $reason,
                ]
            );
        });

        return redirect()
            ->route('admin.grade-approval', [
                'school_year' => $schoolYear,
            ])
            ->with(
                'success',
                'Grade submission successfully returned to the Teacher.'
            );
    }
}
