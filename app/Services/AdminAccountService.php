<?php
namespace App\Services;

use App\Models\User;
use App\Support\SystemOwner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminAccountService
{
    public function __construct(private ActivityRecorder $audit) {}

    public static function columns(): array
    {
        return ['admin_id','name','email'];
    }

    public static function rules(): array
    {
        return ['admin_id'=>'nullable|integer|min:1','name'=>'required|string|max:255','email'=>'required|email|max:255'];
    }

    public function matched(array $data): ?User
    {
        if (!empty($data['admin_id'])) return User::withTrashed()->find((int)$data['admin_id']);
        return User::withTrashed()->where('email',$data['email'])->first();
    }

    public function save(array $data, ?User $existing = null, string $source = 'form', ?string $password = null): User
    {
        return DB::transaction(function () use ($data,$existing,$source,$password) {
            if (($data['name'] ?? null) === 'ghostman' || ($data['email'] ?? null) === SystemOwner::EMAIL) {
                throw ValidationException::withMessages(['name'=>'This account identity is reserved.']);
            }
            if ($existing && ($existing->role !== 'admin' || $existing->trashed())) {
                throw ValidationException::withMessages(['email'=>'This record is not an active administrator account.']);
            }
            if ($existing && $existing->name === 'ghostman') {
                throw ValidationException::withMessages(['name'=>'The special local master account cannot be edited through CSV or user management.']);
            }
            $conflict = User::withTrashed()->where('email',$data['email'])->first();
            if ($conflict && (!$existing || $conflict->id !== $existing->id)) {
                throw ValidationException::withMessages(['email'=>'Email is already assigned to a user account, including deleted accounts.']);
            }
            $before = $existing?->only(['name','email','role']);
            if ($existing) {
                $existing->update(['name'=>$data['name'],'email'=>$data['email']]);
                $user = $existing;
            } else {
                $user = User::create([
                    'name'=>$data['name'],
                    'email'=>$data['email'],
                    'role'=>'admin',
                    'password'=>Hash::make($password ?? Str::random(64)),
                ]);
                $user->update(['username' => 'admin_' . $user->id]);
            }
            $after = $user->only(['name','email','role']);
            if ($before !== $after) $this->audit->record($before ? 'update' : 'create','admins',(string)$user->id,$before,$after,['source'=>$source,'requires_password_setup'=>!$existing && !$password]);
            return $user;
        });
    }

    /** Bulk activate/deactivate administrators, preserving self and system-owner protection. */
    public function setStatuses(array $ids, string $status): int
    {
        if (!in_array($status, ['active', 'inactive'], true)) {
            throw ValidationException::withMessages(['status' => 'Invalid account status.']);
        }
        return DB::transaction(function () use ($ids, $status) {
            $users = User::where('role', 'admin')->whereIn('id', $ids)->lockForUpdate()->get();
            if ($users->count() !== count($ids)) {
                throw ValidationException::withMessages(['ids' => 'One or more administrator accounts were not found.']);
            }
            if ($users->contains(fn (User $user) => $user->id === auth()->id() || SystemOwner::is($user))) {
                throw ValidationException::withMessages(['ids' => 'Your own account and protected accounts cannot be changed in bulk.']);
            }
            if ($status === 'inactive') {
                $currentlyActive = User::where('role', 'admin')->where('is_active', true)->count();
                $toDeactivate = $users->where('is_active', true)->count();
                if ($currentlyActive <= $toDeactivate) {
                    throw ValidationException::withMessages(['ids' => 'At least one active administrator must remain.']);
                }
            }
            $changed = 0;
            foreach ($users as $user) {
                $active = $status === 'active';
                if ((bool) $user->is_active === $active) continue;
                $before = $user->only(['name', 'email', 'role', 'is_active']);
                $user->is_active = $active;
                $user->save();
                $this->audit->record('status_change', 'admins', (string) $user->id, $before, $user->only(['name', 'email', 'role', 'is_active']), ['source' => 'bulk_status']);
                $changed++;
            }
            return $changed;
        });
    }

    public function delete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $users = User::where('role','admin')->whereIn('id',$ids)->lockForUpdate()->get();
            if ($users->count() !== count($ids)) throw ValidationException::withMessages(['ids'=>'One or more administrators do not exist.']);
            if ($users->contains('id',auth()->id()) || $users->contains('name','ghostman')) {
                throw ValidationException::withMessages(['ids'=>'You cannot delete your own account or the special master account.']);
            }
            if (User::where('role','admin')->count() <= $users->count()) {
                throw ValidationException::withMessages(['ids'=>'At least one administrator must remain active.']);
            }
            foreach ($users as $user) {
                $before = $user->only(['name','email','role']);
                $user->delete();
                $this->audit->record('delete','admins',(string)$user->id,$before,null,['recoverable'=>true]);
            }
            return $users->count();
        });
    }

    public function restore(int $id): void
    {
        DB::transaction(function () use ($id) {
            $user = User::onlyTrashed()->where('role','admin')->findOrFail($id);
            $user->restore();
            $this->audit->record('restore','admins',(string)$user->id,null,$user->only(['name','email','role']));
        });
    }

    public function resetPassword(User $account, string $password): void
    {
        DB::transaction(function () use ($account,$password) {
            if ($account->trashed()) throw ValidationException::withMessages(['password'=>'Restore the account before changing its password.']);
            $account->update(['password'=>Hash::make($password)]);
            $this->audit->record('password_reset','users',(string)$account->id,null,null,['account_email'=>$account->email,'role'=>$account->role]);
        });
    }
}
