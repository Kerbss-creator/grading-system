<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradeRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminGradeApprovalController extends Controller
{
    /**
     * Display grade submissions for Admin approval.
     */
    public function index(Request $request)
    {
        $schoolYear = $request->query('school_year', '2026-2027');

        $records = GradeRecord::with([
            'student',
            'teachingAssignment.faculty',
        ])
            ->where('school_year', $schoolYear)
            ->where(function ($query) {
                $query->whereIn('status', [
                    'forwarded_to_admin',
                    'approved',
                ])
                    ->orWhere(function ($query) {
                        $query->where('status', 'returned')
                            ->whereNotNull('admin_return_reason');
                    });
            })
            ->get();

        // Group records into batches by assignment, school year, and term.
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

                $assignment = $latestRecord->teachingAssignment;
                $faculty = $assignment?->faculty;

                $statuses = $group
                    ->pluck('status')
                    ->unique()
                    ->values();

                $batchStatus = $statuses->count() === 1
                    ? $statuses->first()
                    : 'inconsistent';

                return (object) [
                    'teaching_assignment_id' => $latestRecord->teaching_assignment_id,
                    'teachingAssignment' => $assignment,

                    'grade_level' => $assignment?->grade_level,
                    'subject' => $assignment?->subject,

                    'teacher_name' => $faculty
                        ? trim(
                            $faculty->first_name . ' ' .
                            ($faculty->middle_name ? $faculty->middle_name . ' ' : '') .
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
                    'status' => $batchStatus,

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
            'submissions',
            'pendingCount',
            'approvedCount',
            'returnedCount'
        ));
    }

    /**
     * Approve an entire grade submission batch.
     */
    public function approve(
        int $assignmentId,
        string $schoolYear,
        int $term
    ) {
        abort_unless(in_array($term, [1, 2, 3], true), 404);

        DB::transaction(function () use ($assignmentId, $schoolYear, $term) {

            $gradeRecords = GradeRecord::where(
                'teaching_assignment_id',
                $assignmentId
            )
                ->where('school_year', $schoolYear)
                ->where('term', $term)
                ->lockForUpdate()
                ->get();

            abort_if($gradeRecords->isEmpty(), 404, 'No grade records found.');

            $statuses = $gradeRecords
                ->pluck('status')
                ->unique();

            abort_unless(
                $statuses->count() === 1
                && $statuses->first() === 'forwarded_to_admin',
                409,
                'This batch is no longer awaiting Admin approval.'
            );

            GradeRecord::whereIn('id', $gradeRecords->pluck('id'))
                ->update([
                    'status' => 'approved',
                    'admin_return_reason' => null,
                    'admin_reviewed_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        return redirect()
            ->route('admin.grade-approval')
            ->with('success', 'Grade submission successfully approved.');
    }

    /**
     * Return an entire grade submission batch to the Teacher.
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

        DB::transaction(function () use ($assignmentId, $schoolYear, $term, $validated) {

            $gradeRecords = GradeRecord::where(
                'teaching_assignment_id',
                $assignmentId
            )
                ->where('school_year', $schoolYear)
                ->where('term', $term)
                ->lockForUpdate()
                ->get();

            abort_if($gradeRecords->isEmpty(), 404, 'No grade records found.');

            $statuses = $gradeRecords
                ->pluck('status')
                ->unique();

            abort_unless(
                $statuses->count() === 1
                && $statuses->first() === 'forwarded_to_admin',
                409,
                'This batch is no longer awaiting Admin approval.'
            );

            GradeRecord::whereIn('id', $gradeRecords->pluck('id'))
                ->update([
                    'status' => 'returned',
                    'admin_return_reason' => $validated['reason'],
                    'admin_reviewed_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        return redirect()
            ->route('admin.grade-approval')
            ->with('success', 'Grade submission successfully returned to the Teacher.');
    }
}
