<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\GradeRecord;
use App\Services\DatabaseBackupService;
use App\Services\ActivityRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SchoolYearController extends Controller
{
    /**
     * Display school year management page.
     */
    public function index()
    {
        $schoolYears = SchoolYear::orderByDesc('year_name')->get();

        $activeSchoolYear = SchoolYear::active();

        return view('admin.settings', compact(
            'schoolYears',
            'activeSchoolYear'
        ));
    }

    /**
     * Create a new upcoming school year.
     */
    public function store(
        Request $request,
        ActivityRecorder $activityRecorder
    ) {
        $validated = $request->validate([
            'year_name' => [
                'required',
                'string',
                'regex:/^\d{4}-\d{4}$/',
                'unique:school_years,year_name',
            ],
            'start_date' => [
                'required',
                'date',
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
            ],
        ]);

        // Validate consecutive school year.
        [$startYear, $endYear] = array_map(
            'intval',
            explode('-', $validated['year_name'])
        );

        if ($endYear !== $startYear + 1) {
            return back()
                ->withErrors([
                    'year_name' => 'School year must use consecutive years, such as 2027-2028.',
                ])
                ->withInput();
        }

        try {
            DB::transaction(function () use ($validated, $activityRecorder) {
                // Lock existing school year rows to prevent
                // simultaneous overlapping school year creation.
                SchoolYear::query()
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                // Recheck overlapping dates inside the transaction.
                $hasOverlap = SchoolYear::query()
                    ->whereNotNull('start_date')
                    ->whereNotNull('end_date')
                    ->whereDate(
                        'start_date',
                        '<=',
                        $validated['end_date']
                    )
                    ->whereDate(
                        'end_date',
                        '>=',
                        $validated['start_date']
                    )
                    ->exists();

                if ($hasOverlap) {
                    throw new \RuntimeException(
                        'The selected dates overlap with an existing school year.'
                    );
                }

                $schoolYear = SchoolYear::create([
                    'year_name' => $validated['year_name'],
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'status' => SchoolYear::STATUS_UPCOMING,
                    'current_term' => 1,
                ]);

                $activityRecorder->record(
                    'school_year_created',
                    'school_year',
                    (string) $schoolYear->id,
                    null,
                    [
                        'year_name' => $schoolYear->year_name,
                        'start_date' => $schoolYear->start_date?->toDateString(),
                        'end_date' => $schoolYear->end_date?->toDateString(),
                        'status' => $schoolYear->status,
                        'current_term' => $schoolYear->current_term,
                    ]
                );
            });

        } catch (\RuntimeException $exception) {
            return back()
                ->withErrors([
                    'start_date' => $exception->getMessage(),
                ])
                ->withInput();
        }

        return redirect()
            ->route('admin.settings')
            ->with('success', 'School year added successfully.');
    }

    /**
     * Update an upcoming school year's dates.
     */
    public function update(
        Request $request,
        SchoolYear $schoolYear,
        ActivityRecorder $activityRecorder
    ) {
        $validated = $request->validate([
            'start_date' => [
                'required',
                'date',
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
            ],
        ]);

        try {
            $updated = DB::transaction(function () use ($schoolYear, $validated, $activityRecorder) {
                // Lock school year rows in a consistent order.
                SchoolYear::query()
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $lockedSchoolYear = SchoolYear::query()
                    ->findOrFail($schoolYear->id);

                // Only active and upcoming school years may be edited.
                if (
                    !in_array($lockedSchoolYear->status, [
                        SchoolYear::STATUS_ACTIVE,
                        SchoolYear::STATUS_UPCOMING,
                    ], true)
                ) {
                    return false;
                }

                // Recheck overlapping dates inside the transaction.
                $hasOverlap = SchoolYear::query()
                    ->where('id', '!=', $lockedSchoolYear->id)
                    ->whereNotNull('start_date')
                    ->whereNotNull('end_date')
                    ->whereDate(
                        'start_date',
                        '<=',
                        $validated['end_date']
                    )
                    ->whereDate(
                        'end_date',
                        '>=',
                        $validated['start_date']
                    )
                    ->exists();

                if ($hasOverlap) {
                    throw new \RuntimeException(
                        'The selected dates overlap with another existing school year.'
                    );
                }

                $before = [
                    'year_name' => $lockedSchoolYear->year_name,
                    'start_date' => $lockedSchoolYear->start_date?->toDateString(),
                    'end_date' => $lockedSchoolYear->end_date?->toDateString(),
                    'status' => $lockedSchoolYear->status,
                ];

                $lockedSchoolYear->update([
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                ]);

                $after = [
                    'year_name' => $lockedSchoolYear->year_name,
                    'start_date' => $lockedSchoolYear->start_date?->toDateString(),
                    'end_date' => $lockedSchoolYear->end_date?->toDateString(),
                    'status' => $lockedSchoolYear->status,
                ];

                $activityRecorder->record(
                    'school_year_dates_updated',
                    'school_year',
                    (string) $lockedSchoolYear->id,
                    $before,
                    $after
                );

                return true;
            });

        } catch (\RuntimeException $exception) {
            return back()
                ->withErrors([
                    'start_date' => $exception->getMessage(),
                ])
                ->withInput();
        }

        if (!$updated) {
            return back()->with(
                'error',
                'This school year can no longer be edited because it is completed.'
            );
        }

        return redirect()
            ->route('admin.settings')
            ->with(
                'success',
                $schoolYear->year_name . ' dates updated successfully.'
            );
    }

    /**
     * Activate an upcoming school year.
     *
     * Requirements:
     * - The current active school year must have ended.
     * - The target school year must be upcoming.
     * - No submitted or forwarded grade records may remain
     *   in the school year being completed.
     * - A full database backup must be created first.
     */
    public function activate(
        SchoolYear $schoolYear,
        DatabaseBackupService $backupService,
        ActivityRecorder $activityRecorder
    ) {
        // Initial target status check.
        if ($schoolYear->status !== SchoolYear::STATUS_UPCOMING) {
            return back()->with(
                'error',
                'Only upcoming school years can be activated.'
            );
        }

        if (!$schoolYear->start_date || !$schoolYear->end_date) {
            return back()->with(
                'error',
                'Set valid Start Date and End Date before activating this school year.'
            );
        }

        $activeSchoolYear = SchoolYear::active();

        if (!$activeSchoolYear) {
            return back()->with(
                'error',
                'No active school year was found. Please verify the school year settings first.'
            );
        }

        if ($activeSchoolYear->id === $schoolYear->id) {
            return back()->with(
                'info',
                'This school year is already active.'
            );
        }

        if (!$activeSchoolYear->end_date) {
            return back()->with(
                'error',
                'The active school year has no End Date. Please update its dates first.'
            );
        }

        // The current active school year must have ended.
        if (today()->lessThanOrEqualTo($activeSchoolYear->end_date)) {
            return back()->with(
                'error',
                'The active school year has not ended yet. Activation is not allowed.'
            );
        }

        // Check pending grades before creating a backup.
        $hasPendingGrades = GradeRecord::query()
            ->where('school_year', $activeSchoolYear->year_name)
            ->whereIn('status', [
                'submitted',
                'forwarded_to_admin',
            ])
            ->exists();

        if ($hasPendingGrades) {
            return back()->with(
                'error',
                'School year activation is blocked because there are grade submissions waiting for Adviser review or Admin approval.'
            );
        }

        try {
            // Create a full database backup BEFORE activation.
            $backupPath = $backupService->createFullBackup(
                $activeSchoolYear->year_name
            );

            // Verify that the backup file exists and is not empty.
            if (
                !is_file($backupPath) ||
                filesize($backupPath) <= 0
            ) {
                throw new \RuntimeException(
                    'The database backup could not be verified.'
                );
            }

            DB::transaction(function () use ($schoolYear, $activityRecorder) {
                /*
                 * Lock school year rows in a consistent order.
                 *
                 * Teacher submission, Adviser review, and Admin
                 * approval also lock the active SchoolYear row.
                 * This coordinates those actions with activation.
                 */
                $lockedSchoolYears = SchoolYear::query()
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $targetSchoolYear = $lockedSchoolYears
                    ->firstWhere('id', $schoolYear->id);

                $currentActiveYear = $lockedSchoolYears
                    ->firstWhere(
                        'status',
                        SchoolYear::STATUS_ACTIVE
                    );

                if (!$targetSchoolYear) {
                    throw new \RuntimeException(
                        'The target school year could not be found.'
                    );
                }

                // Recheck target status inside the transaction.
                if (
                    $targetSchoolYear->status !==
                    SchoolYear::STATUS_UPCOMING
                ) {
                    throw new \RuntimeException(
                        'The target school year is no longer upcoming.'
                    );
                }

                if (!$currentActiveYear) {
                    throw new \RuntimeException(
                        'The active school year could not be found.'
                    );
                }

                if (
                    $currentActiveYear->id ===
                    $targetSchoolYear->id
                ) {
                    throw new \RuntimeException(
                        'The selected school year is already active.'
                    );
                }

                if (!$currentActiveYear->end_date) {
                    throw new \RuntimeException(
                        'The active school year has no End Date.'
                    );
                }

                // Recheck the current End Date.
                if (
                    today()->lessThanOrEqualTo(
                        $currentActiveYear->end_date
                    )
                ) {
                    throw new \RuntimeException(
                        'The active school year has not ended yet.'
                    );
                }

                /*
                 * Recheck pending submissions inside the transaction.
                 *
                 * Only submitted and forwarded_to_admin block activation.
                 * Draft and returned records are preserved.
                 */
                $hasPendingGrades = GradeRecord::query()
                    ->where(
                        'school_year',
                        $currentActiveYear->year_name
                    )
                    ->whereIn('status', [
                        'submitted',
                        'forwarded_to_admin',
                    ])
                    ->lockForUpdate()
                    ->exists();

                if ($hasPendingGrades) {
                    throw new \RuntimeException(
                        'Pending grade submissions must be reviewed before activation.'
                    );
                }

                $previousYear = $currentActiveYear->year_name;
                $targetYear = $targetSchoolYear->year_name;

                // Mark the previous school year as completed.
                $currentActiveYear->update([
                    'status' => SchoolYear::STATUS_COMPLETED,
                ]);

                // Activate the target school year.
                $targetSchoolYear->update([
                    'status' => SchoolYear::STATUS_ACTIVE,
                ]);

                // Record activation in the audit log.
                $activityRecorder->record(
                    'school_year_activated',
                    'school_year',
                    (string) $targetSchoolYear->id,
                    [
                        'year_name' => $targetYear,
                        'status' => SchoolYear::STATUS_UPCOMING,
                    ],
                    [
                        'year_name' => $targetYear,
                        'status' => SchoolYear::STATUS_ACTIVE,
                    ],
                    [
                        'previous_school_year' => $previousYear,
                        'previous_school_year_status' =>
                            SchoolYear::STATUS_COMPLETED,
                        'activated_school_year' => $targetYear,
                    ]
                );
            });

        } catch (\Throwable $exception) {

            Log::error('School year activation failed.', [
                'school_year_id' => $schoolYear->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with(
                'error',
                'School year activation was cancelled because the backup failed or an activation requirement was not satisfied. No school year change was completed.'
            );
        }

        return redirect()
            ->route('admin.settings')
            ->with(
                'success',
                $schoolYear->year_name .
                ' is now the active school year. Previous school year backup was created successfully.'
            );
    }

    /**
     * Update the current term of the active school year.
     */
    public function updateTerm(
        Request $request,
        SchoolYear $schoolYear,
        ActivityRecorder $activityRecorder
    ) {
        $validated = $request->validate([
            'current_term' => [
                'required',
                'integer',
                Rule::in([1, 2, 3]),
            ],
        ]);

        $updated = DB::transaction(function () use ($schoolYear, $validated, $activityRecorder) {
            // Lock the selected school year before changing its term.
            $lockedSchoolYear = SchoolYear::query()
                ->lockForUpdate()
                ->findOrFail($schoolYear->id);

            if (!$lockedSchoolYear->isActive()) {
                return false;
            }

            $before = [
                'year_name' => $lockedSchoolYear->year_name,
                'current_term' => $lockedSchoolYear->current_term,
            ];

            $lockedSchoolYear->update([
                'current_term' => $validated['current_term'],
            ]);

            $after = [
                'year_name' => $lockedSchoolYear->year_name,
                'current_term' => $lockedSchoolYear->current_term,
            ];

            $activityRecorder->record(
                'school_year_term_updated',
                'school_year',
                (string) $lockedSchoolYear->id,
                $before,
                $after
            );

            return true;
        });

        if (!$updated) {
            return back()->with(
                'error',
                'Only the active school year can change its current term.'
            );
        }

        return redirect()
            ->route('admin.settings')
            ->with('success', 'Current term updated successfully.');
    }
}
