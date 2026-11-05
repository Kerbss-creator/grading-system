<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Student;
use App\Models\Faculty;
use App\Models\User;
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

        // Refresh the corresponding CSV snapshot.
        $snapshots->refresh($type);

        return back()->with(
            'success',
            'Record and its login account restored.'
        );
    }
}
