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
     * Display activity logs.
     */
    public function index(Request $request)
    {
        $query = ActivityLog::query()->latest('id');

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
                        $q->whereRaw('1=1');
                    }
                });
        }

        if (in_array(
            $request->query('type'),
            ['students', 'faculty', 'admins', 'users'],
            true
        )) {
            $query->where('record_type', $request->query('type'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->query('action'));
        }

        return view('admin.activity-log', [
            'logs' => $query->paginate(30)->withQueryString(),
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

                $account->restore();

                $audit->record(
                    'restore',
                    'users',
                    (string) $id,
                    null,
                    $account->only(['name', 'email', 'role'])
                );
            });

            return back()->with(
                'success',
                'Unlinked login account restored.'
            );
        }

        // Restore an administrator account.
        if ($type === 'admins') {
            $account = User::withTrashed()->findOrFail($id);

            abort_if(SystemOwner::is($account), 404);

            $admins->restore($id);
        } else {
            // Restore a student or faculty record and its linked account.
            $records->restore($type, $id);
        }

        $snapshots->refresh($type);

        return back()->with(
            'success',
            'Record and its login account restored.'
        );
    }

    /**
     * Permanently delete one record from the Recycle Bin.
     */
    public function forceDelete(
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

        if (in_array($type, ['students', 'faculty'], true)) {
            $snapshots->refresh($type);
        }

        return back()->with(
            'success',
            'Record permanently deleted.'
        );
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
            in_array($type, ['students', 'faculty'], true)
        ) {
            $snapshots->refresh($type);
        }

        $response = back();

        if ($deletedCount > 0) {
            $response = $response->with(
                'success',
                "{$deletedCount} record(s) permanently deleted."
            );
        }

        if (count($skipped) > 0) {
            $response = $response->with(
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

                    if (!$account || !in_array($account->role, $validRoles, true)) {
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

            // Keep an audit entry for every successful permanent deletion.
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
