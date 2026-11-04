<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\PersonRecordService;
use App\Services\AdminAccountService;
use App\Services\SchoolCsvSnapshotService;
use App\Models\User;
use App\Support\SystemOwner;
use Illuminate\Http\Request;
use App\Services\ActivityRecorder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query()->latest('id');
        if (!SystemOwner::is($request->user())) {
            $owner = User::withTrashed()->where('email',SystemOwner::EMAIL)->where('role','admin')->first();
            $query->where(fn($q)=>$q->whereNull('actor_email')->orWhere('actor_email','!=',SystemOwner::EMAIL))
                ->where('actor_name','!=','ghostman')
                ->where(function ($q) use ($owner) {
                    if ($owner) $q->where(fn($inner)=>$inner->whereNotIn('record_type',['admins','users'])->orWhereNull('record_key')->orWhere('record_key','!=',(string)$owner->id));
                    else $q->whereRaw('1=1');
                });
        }
        if (in_array($request->query('type'), ['students','teachers','admins','users'], true)) $query->where('record_type', $request->query('type'));
        if ($request->filled('action')) $query->where('action', $request->query('action'));
        return view('admin.activity-log', ['logs' => $query->paginate(30)->withQueryString()]);
    }

    public function trash()
    {
        return view('admin.recycle-bin', [
            'students' => Student::onlyTrashed()->orderByDesc('deleted_at')->get(),
            'teachers' => Teacher::onlyTrashed()->orderByDesc('deleted_at')->get(),
            'admins' => SystemOwner::visibleTo(User::onlyTrashed()->where('role','admin'),auth()->user())->orderByDesc('deleted_at')->get(),
            'users' => User::onlyTrashed()->whereIn('role',['student','teacher'])
                ->whereNotIn('id',Student::withTrashed()->whereNotNull('user_id')->select('user_id'))
                ->whereNotIn('id',Teacher::withTrashed()->whereNotNull('user_id')->select('user_id'))
                ->orderByDesc('deleted_at')->get(),
        ]);
    }

    public function restore(string $type, int $id, PersonRecordService $records, AdminAccountService $admins, SchoolCsvSnapshotService $snapshots, ActivityRecorder $audit)
    {
        abort_unless(in_array($type, ['students','teachers','admins','users'], true), 404);
        if ($type === 'users') {
            DB::transaction(function () use ($id,$audit) {
                $account = User::onlyTrashed()->whereIn('role',['student','teacher'])->findOrFail($id);
                if (Student::withTrashed()->where('user_id',$id)->exists() || Teacher::withTrashed()->where('user_id',$id)->exists()) {
                    throw ValidationException::withMessages(['user'=>'Restore the linked school record instead.']);
                }
                $account->restore();
                $audit->record('restore','users',(string)$id,null,$account->only(['name','email','role']));
            });
            return back()->with('success','Unlinked login account restored.');
        }
        if ($type === 'admins') {
            $account = User::withTrashed()->findOrFail($id);
            abort_if(SystemOwner::is($account),404);
            $admins->restore($id);
        }
        else $records->restore($type, $id);
        $snapshots->refresh($type);
        return back()->with('success', 'Record and its login account restored.');
    }
}
