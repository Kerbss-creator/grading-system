<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Student;
use App\Models\Faculty;
use App\Models\User;
use App\Models\GradeRecord;
use App\Models\TeachingAssignment;
use App\Services\PersonRecordService;
use App\Services\AdminAccountService;
use App\Services\SchoolCsvSnapshotService;
use App\Services\ActivityRecorder;
use App\Support\SystemOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActivityLogController extends Controller
{
    /**
     * Apply System Owner visibility restrictions to activity logs.
     */
    private function visibleLogsQuery(Request $request)
    {
        $query = ActivityLog::query();

        if (!SystemOwner::is($request->user())) {
            $owner = User::withTrashed()
                ->where('email', SystemOwner::EMAIL)
                ->where('role', 'admin')
                ->first();

            $query
                ->where(function ($q) {
                    $q->whereNull('actor_email')
                        ->orWhere('actor_email', '!=', SystemOwner::EMAIL);
                })
                ->where('actor_name', '!=', 'ghostman')
                ->where(function ($q) use ($owner) {
                    if ($owner) {
                        $q->where(function ($inner) use ($owner) {
                            $inner->whereNotIn('record_type', ['admins', 'users'])
                                ->orWhereNull('record_key')
                                ->orWhere('record_key', '!=', (string) $owner->id);
                        });
                    } else {
                        $q->whereRaw('1 = 1');
                    }
                });
        }

        return $query;
    }

    /**
     * Display activity logs.
     */
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:50'],
            'action' => ['nullable', 'string', 'max:40'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $baseQuery = $this->visibleLogsQuery($request);

        $totalActivities = (clone $baseQuery)->count();

        $activeUsers = (clone $baseQuery)
            ->whereNotNull('actor_id')
            ->where('created_at', '>=', now()->subDays(30))
            ->distinct()
            ->count('actor_id');

        $todayLogs = (clone $baseQuery)
            ->whereDate('created_at', today())
            ->count();

        $availableTypes = (clone $baseQuery)
            ->whereNotNull('record_type')
            ->distinct()
            ->orderBy('record_type')
            ->pluck('record_type');

        $availableActions = (clone $baseQuery)
            ->whereNotNull('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $query = clone $baseQuery;

        if ($request->filled('search')) {
            $search = $request->query('search');

            $query->where(function ($q) use ($search) {
                $q->where('actor_name', 'like', "%{$search}%")
                    ->orWhere('actor_email', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('record_type', 'like', "%{$search}%")
                    ->orWhere('record_key', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('record_type', $request->query('type'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->query('action'));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->query('to'));
        }

        $logs = $query
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        /*
         * Add display-only aliases for the Blade.
         * Role is not stored historically in activity_logs,
         * so we intentionally do not invent a role value.
         */
        $logs->getCollection()->transform(function (ActivityLog $log) {
            $log->user_name = $log->actor_name;
            $log->user_role = $log->actor_role
                ? ucwords(str_replace('_', ' ', $log->actor_role))
                : 'Not recorded';
            $log->module = ucwords(
                str_replace('_', ' ', $log->record_type ?? 'Unknown')
            );
            $log->description = data_get(
                $log->details,
                'description',
                $log->action . ' - ' . ($log->record_type ?? 'record')
            );

            return $log;
        });

        return view('admin.audit-log', [
            'logs' => $logs,
            'totalActivities' => $totalActivities,
            'activeUsers' => $activeUsers,
            'todayLogs' => $todayLogs,
            'availableTypes' => $availableTypes,
            'availableActions' => $availableActions,
            'filters' => $request->only([
                'search',
                'type',
                'action',
                'from',
                'to',
            ]),
        ]);
    }

    /**
     * Return the details of one visible audit log as JSON.
     */
    public function show(Request $request, ActivityLog $log)
    {
        $log = $this->visibleLogsQuery($request)
            ->whereKey($log->id)
            ->firstOrFail();

        return response()->json([
            'id' => $log->id,
            'actor_id' => $log->actor_id,
            'actor_name' => $log->actor_name,
            'actor_email' => $log->actor_email,
            'actor_role' => $log->actor_role,
            'action' => $log->action,
            'record_type' => $log->record_type,
            'record_key' => $log->record_key,
            'before' => $log->before,
            'after' => $log->after,
            'details' => $log->details,
            'ip_address' => $log->ip_address,
            'created_at' => $log->created_at?->format('M d, Y h:i A'),
        ]);
    }

    /**
     * Display deleted records in the Recycle Bin.
     */
    public function trash()
    {
        return view('admin.recycle-bin', [
            'students' => Student::onlyTrashed()
                ->orderByDesc('deleted_at')
                ->get(),

            'faculty' => Faculty::onlyTrashed()
                ->orderByDesc('deleted_at')
                ->get(),

            'admins' => SystemOwner::visibleTo(
                User::onlyTrashed()->where('role', 'admin'),
                auth()->user()
            )
                ->orderByDesc('deleted_at')
                ->get(),

            'users' => User::onlyTrashed()
                ->whereIn('role', [
                    'student',
                    'teacher',
                    'adviser',
                    'teacher_adviser',
                ])
                ->whereNotIn(
                    'id',
                    Student::withTrashed()
                        ->whereNotNull('user_id')
                        ->select('user_id')
                )
                ->whereNotIn(
                    'id',
                    Faculty::withTrashed()
                        ->whereNotNull('user_id')
                        ->select('user_id')
                )
                ->orderByDesc('deleted_at')
                ->get(),
        ]);
    }

    /**
     * Restore a deleted record or unlinked login account.
     */
    public function restore(
        Request $request,
        string $type,
        int $id,
        PersonRecordService $records,
        AdminAccountService $admins,
        SchoolCsvSnapshotService $snapshots,
        ActivityRecorder $audit
    ) {
        abort_unless(
            in_array($type, ['students', 'faculty', 'admins', 'users'], true),
            404
        );

        $tab = $request->input('tab', $type);

        if (!in_array($tab, ['students', 'faculty', 'admins', 'users'], true)) {
            $tab = $type;
        }

        // Restore an unlinked login account.
        if ($type === 'users') {
            DB::transaction(function () use ($id, $audit) {
                $account = User::onlyTrashed()
                    ->whereIn('role', [
                        'student',
                        'teacher',
                        'adviser',
                        'teacher_adviser',
                    ])
                    ->lockForUpdate()
                    ->findOrFail($id);

                $hasStudentRecord = Student::withTrashed()
                    ->where('user_id', $id)
                    ->exists();

                $hasFacultyRecord = Faculty::withTrashed()
                    ->where('user_id', $id)
                    ->exists();

                if ($hasStudentRecord || $hasFacultyRecord) {
                    throw ValidationException::withMessages([
                        'user' => 'Restore the linked school record instead.',
                    ]);
                }

                $before = $account->only([
                    'name',
                    'email',
                    'role',
                    'account_status',
                ]);

                $account->restore();

                $audit->record(
                    'restore',
                    'users',
                    (string) $id,
                    null,
                    $before
                );
            });

            return redirect()
                ->route('admin.recycle-bin', ['tab' => $tab])
                ->with('success', 'Unlinked login account restored.');
        }

        // Restore an administrator account.
        if ($type === 'admins') {
            $account = User::withTrashed()
                ->where('role', 'admin')
                ->findOrFail($id);

            abort_if(SystemOwner::is($account), 404);

            $admins->restore($id);
            $snapshots->refresh('admins');
        } else {
            // PersonRecordService handles linked account restoration and audit logging.
            $records->restore($type, $id);
            $snapshots->refresh($type);
        }

        return redirect()
            ->route('admin.recycle-bin', ['tab' => $tab])
            ->with('success', 'Record and its login account restored.');
    }

    /**
     * Permanently delete one record from the Recycle Bin.
     */
    public function forceDelete(
        Request $request,
        string $type,
        int $id,
        ActivityRecorder $audit,
        SchoolCsvSnapshotService $snapshots
    ) {
        abort_unless(
            in_array($type, ['students', 'faculty', 'admins', 'users'], true),
            404
        );

        $this->permanentlyDeleteRecord($type, $id, $audit);

        if (in_array($type, ['students', 'faculty', 'admins'], true)) {
            $snapshots->refresh($type);
        }

        $tab = $request->input('tab', $type);

        if (!in_array($tab, ['students', 'faculty', 'admins', 'users'], true)) {
            $tab = $type;
        }

        return redirect()
            ->route('admin.recycle-bin', ['tab' => $tab])
            ->with('success', 'Record permanently deleted.');
    }

    /**
     * Permanently delete selected records from the Recycle Bin.
     * Protected or invalid records are skipped.
     */
    public function forceDeleteSelected(
        Request $request,
        string $type,
        ActivityRecorder $audit,
        SchoolCsvSnapshotService $snapshots
    ) {
        abort_unless(
            in_array($type, ['students', 'faculty', 'admins', 'users'], true),
            404
        );

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $deletedCount = 0;
        $skipped = [];

        foreach ($validated['ids'] as $id) {
            try {
                $this->permanentlyDeleteRecord(
                    $type,
                    (int) $id,
                    $audit
                );

                $deletedCount++;
            } catch (ValidationException $exception) {
                $messages = collect($exception->errors())
                    ->flatten()
                    ->unique()
                    ->implode(' ');

                $skipped[] = "ID {$id}: {$messages}";
            }
        }

        if (
            $deletedCount > 0 &&
            in_array($type, ['students', 'faculty', 'admins'], true)
        ) {
            $snapshots->refresh($type);
        }

        $tab = $request->input('tab', $type);

        if (!in_array($tab, ['students', 'faculty', 'admins', 'users'], true)) {
            $tab = $type;
        }

        $response = redirect()
            ->route('admin.recycle-bin', ['tab' => $tab]);

        if ($deletedCount > 0) {
            $response->with(
                'success',
                "{$deletedCount} record(s) permanently deleted."
            );
        }

        if (count($skipped) > 0) {
            $response->with(
                'warning',
                'Some records were skipped: ' . implode(' | ', $skipped)
            );
        }

        return $response;
    }

    /**
     * Shared permanent-delete logic.
     */
    private function permanentlyDeleteRecord(
        string $type,
        int $id,
        ActivityRecorder $audit
    ): void {
        DB::transaction(function () use ($type, $id, $audit) {
            $before = [];
            $recordKey = (string) $id;

            /*
             * STUDENTS
             */
            if ($type === 'students') {
                $student = Student::onlyTrashed()
                    ->lockForUpdate()
                    ->find($id);

                if (!$student) {
                    throw ValidationException::withMessages([
                        'record' => 'Student record was not found in the Recycle Bin.',
                    ]);
                }

                if (GradeRecord::where('student_id', $student->id)->exists()) {
                    throw ValidationException::withMessages([
                        'record' => 'Student cannot be permanently deleted because grade records are associated with this student.',
                    ]);
                }

                $account = null;

                if ($student->user_id) {
                    $account = User::withTrashed()
                        ->lockForUpdate()
                        ->find($student->user_id);

                    if (!$account || $account->role !== 'student') {
                        throw ValidationException::withMessages([
                            'record' => 'The linked student login account is missing or has an invalid role. Deletion was stopped to protect account data.',
                        ]);
                    }

                    $linkedToFaculty = Faculty::withTrashed()
                        ->where('user_id', $account->id)
                        ->exists();

                    if ($linkedToFaculty) {
                        throw ValidationException::withMessages([
                            'record' => 'The linked login account is also associated with a Faculty record. Deletion was stopped.',
                        ]);
                    }
                }

                $before = $student->only([
                    'id',
                    'student_id',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'email',
                    'grade_level',
                    'school_year',
                    'user_id',
                    'deleted_at',
                ]);

                $recordKey = (string) $student->student_id;

                $student->forceDelete();

                if ($account) {
                    $account->forceDelete();
                }
            }

            /*
             * FACULTY
             */ elseif ($type === 'faculty') {
                $faculty = Faculty::onlyTrashed()
                    ->lockForUpdate()
                    ->find($id);

                if (!$faculty) {
                    throw ValidationException::withMessages([
                        'record' => 'Faculty record was not found in the Recycle Bin.',
                    ]);
                }

                $hasGradeRecords = GradeRecord::whereIn(
                    'teaching_assignment_id',
                    TeachingAssignment::where('faculty_id', $faculty->id)
                        ->select('id')
                )->exists();

                if ($hasGradeRecords) {
                    throw ValidationException::withMessages([
                        'record' => 'Faculty cannot be permanently deleted because grade records are associated with their teaching assignments.',
                    ]);
                }

                $account = null;

                if ($faculty->user_id) {
                    $account = User::withTrashed()
                        ->lockForUpdate()
                        ->find($faculty->user_id);

                    $validRoles = [
                        'teacher',
                        'adviser',
                        'teacher_adviser',
                    ];

                    if (
                        !$account ||
                        !in_array($account->role, $validRoles, true)
                    ) {
                        throw ValidationException::withMessages([
                            'record' => 'The linked Faculty login account is missing or has an invalid role. Deletion was stopped to protect account data.',
                        ]);
                    }

                    $linkedToStudent = Student::withTrashed()
                        ->where('user_id', $account->id)
                        ->exists();

                    if ($linkedToStudent) {
                        throw ValidationException::withMessages([
                            'record' => 'The linked login account is also associated with a Student record. Deletion was stopped.',
                        ]);
                    }
                }

                $before = $faculty->only([
                    'id',
                    'faculty_id',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'email',
                    'role',
                    'user_id',
                    'deleted_at',
                ]);

                $recordKey = (string) $faculty->faculty_id;

                $faculty->forceDelete();

                if ($account) {
                    $account->forceDelete();
                }
            }

            /*
             * ADMINS
             */ elseif ($type === 'admins') {
                $account = User::onlyTrashed()
                    ->where('role', 'admin')
                    ->lockForUpdate()
                    ->find($id);

                if (!$account) {
                    throw ValidationException::withMessages([
                        'record' => 'Admin account was not found in the Recycle Bin.',
                    ]);
                }

                if (
                    SystemOwner::is($account) ||
                    (int) $account->id === (int) auth()->id()
                ) {
                    throw ValidationException::withMessages([
                        'record' => 'This Admin account is protected and cannot be permanently deleted.',
                    ]);
                }

                $hasActiveAdmin = User::where('role', 'admin')
                    ->where('account_status', 'active')
                    ->exists();

                if (!$hasActiveAdmin) {
                    throw ValidationException::withMessages([
                        'record' => 'Deletion is blocked because at least one active Admin account must remain.',
                    ]);
                }

                $before = $account->only([
                    'id',
                    'name',
                    'email',
                    'role',
                    'account_status',
                    'deleted_at',
                ]);

                $recordKey = (string) $account->id;

                $account->forceDelete();
            }

            /*
             * UNLINKED USERS
             */ elseif ($type === 'users') {
                $account = User::onlyTrashed()
                    ->whereIn('role', [
                        'student',
                        'teacher',
                        'adviser',
                        'teacher_adviser',
                    ])
                    ->lockForUpdate()
                    ->find($id);

                if (!$account) {
                    throw ValidationException::withMessages([
                        'record' => 'Unlinked login account was not found in the Recycle Bin.',
                    ]);
                }

                if (SystemOwner::is($account)) {
                    throw ValidationException::withMessages([
                        'record' => 'This account is protected and cannot be permanently deleted.',
                    ]);
                }

                $hasStudentRecord = Student::withTrashed()
                    ->where('user_id', $account->id)
                    ->exists();

                $hasFacultyRecord = Faculty::withTrashed()
                    ->where('user_id', $account->id)
                    ->exists();

                if ($hasStudentRecord || $hasFacultyRecord) {
                    throw ValidationException::withMessages([
                        'record' => 'This account is linked to a Student or Faculty record. Delete the linked school record instead.',
                    ]);
                }

                $before = $account->only([
                    'id',
                    'name',
                    'email',
                    'role',
                    'account_status',
                    'deleted_at',
                ]);

                $recordKey = (string) $account->id;

                $account->forceDelete();
            } else {
                abort(404);
            }

            $audit->record(
                'force_delete',
                $type,
                $recordKey,
                $before,
                null,
                [
                    'source' => 'recycle_bin',
                    'permanent' => true,
                ]
            );
        });
    }
}
