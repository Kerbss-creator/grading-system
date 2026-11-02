<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\PersonRecordService;
use App\Services\ActivityRecorder;
use App\Support\SystemOwner;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Services\AdminAccountService;
use App\Services\SchoolCsvSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    public function __construct(private AdminAccountService $admins,private SchoolCsvSnapshotService $csv) {}

    public function index(Request $request)
    {
        $query = SystemOwner::visibleTo(User::query(), $request->user())->orderBy('role')->orderBy('name');
        if (in_array($request->query('role'),['student','teacher','admin'],true)) $query->where('role',$request->query('role'));
        if ($request->filled('search')) {
            $term = trim((string)$request->query('search'));
            $query->where(fn($q)=>$q->where('name','like','%'.$term.'%')->orWhere('email','like','%'.$term.'%')->orWhere('username','like','%'.$term.'%'));
        }
        return view('admin.user-management',[
            'accounts'=>$query->paginate(25)->withQueryString(),
            'counts'=>SystemOwner::visibleTo(User::query(), $request->user())->selectRaw('role,COUNT(*) AS total')->groupBy('role')->pluck('total','role'),
        ]);
    }

    public function adminsIndex(Request $request)
    {
        $query = SystemOwner::visibleTo(User::query(), $request->user())
            ->where('role', 'admin')->orderBy('name');
        if ($request->filled('search')) {
            $term = trim((string) $request->query('search'));
            $query->where(fn ($q) => $q->where('name','like','%'.$term.'%')
                ->orWhere('email','like','%'.$term.'%')->orWhere('username','like','%'.$term.'%'));
        }
        return view('admin.admin-management', [
            'accounts' => $query->paginate(25)->withQueryString(),
        ]);
    }

    public function storeAdmin(Request $request)
    {
        $data = $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'required|email|max:255',
            'password'=>['required','confirmed',Password::min(12)->mixedCase()->numbers()],
        ]);
        $this->admins->save($data,null,'admin_form',$data['password']);
        $this->csv->refresh('admins');
        return back()->with('success','Administrator account created.');
    }

    public function updateAdmin(Request $request,User $user)
    {
        abort_unless($user->role === 'admin' && !SystemOwner::is($user),404);
        $data = $request->validate(['name'=>'required|string|max:255','email'=>'required|email|max:255']);
        $this->admins->save($data,$user,'admin_form');
        $this->csv->refresh('admins');
        return back()->with('success','Administrator information updated.');
    }

    public function resetPassword(Request $request,User $user)
    {
        abort_if(SystemOwner::is($user), 404);
        $data = $request->validate(['password'=>['required','confirmed',Password::min(12)->mixedCase()->numbers()]]);
        $this->admins->resetPassword($user,$data['password']);
        return back()->with('success','Password changed. Do not store passwords in CSV.');
    }

    public function bulkStatusAdmins(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1|max:100',
            'ids.*' => 'required|integer|distinct|min:1',
            'status' => 'required|in:active,inactive',
        ]);
        $count = $this->admins->setStatuses(array_map('intval', $data['ids']), $data['status']);
        $this->csv->refresh('admins');
        return response()->json(['success' => true, 'message' => "$count administrator accounts updated to " . $data['status'] . ".", 'changed' => $count]);
    }

    public function bulkDelete(Request $request)
    {
        $data = $request->validate(['ids'=>'required|array|min:1|max:100','ids.*'=>'integer|min:1|distinct']);
        $count = $this->admins->delete(array_map('intval',$data['ids']));
        $this->csv->refresh('admins');
        return back()->with('success',"$count administrator accounts moved to Recycle Bin.");
    }

    public function bulkDeleteAccounts(Request $request, PersonRecordService $records, ActivityRecorder $audit)
    {
        $data = $request->validate(['ids'=>'required|array|min:1|max:100','ids.*'=>'required|integer|min:1|distinct']);
        $ids = array_map('intval',$data['ids']);
        $types = DB::transaction(function () use ($ids,$request,$records,$audit) {
            $accounts = User::whereIn('id',$ids)->lockForUpdate()->get();
            if ($accounts->count() !== count($ids)) throw ValidationException::withMessages(['ids'=>'One or more accounts were not found. Reload this page and try again.']);
            if ($accounts->contains(fn(User $user)=>$user->id === $request->user()->id || SystemOwner::is($user))) {
                throw ValidationException::withMessages(['ids'=>'Your own account and protected accounts cannot be selected.']);
            }
            $affected = [];
            foreach ($accounts as $account) {
                if ($account->role === 'admin') {
                    $this->admins->delete([$account->id]);
                    $affected['admins'] = true;
                    continue;
                }
                if (!in_array($account->role,['student','teacher'],true)) {
                    throw ValidationException::withMessages(['ids'=>'This selection includes an unsupported account role.']);
                }
                $type = $account->role === 'student' ? 'students' : 'teachers';
                $model = $type === 'students' ? Student::class : Teacher::class;
                $record = $model::where('user_id',$account->id)->first();
                if (!$record) $record = $model::where('email',$account->email)->whereNull('user_id')->first();
                if ($record) {
                    $records->delete($type,[$record->id],'user_management');
                } else {
                    $account->delete();
                    $audit->record('delete','users',(string)$account->id,$account->only(['name','email','role']),null,['source'=>'user_management','recoverable'=>true]);
                }
                $affected[$type] = true;
            }
            return array_keys($affected);
        });
        foreach ($types as $type) $this->csv->refresh($type);
        return back()->with('success',count($ids).' accounts moved to Recycle Bin.');
    }

}
