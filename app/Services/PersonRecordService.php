<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PersonRecordService
{
    public function __construct(private ActivityRecorder $audit) {}

    public static function model(string $type): string
    {
        return match ($type) {
            'students' => Student::class,
            'teachers' => Teacher::class,
            default => throw new \InvalidArgumentException('Unsupported record type.'),
        };
    }

    public static function key(string $type): string
    {
        return $type === 'students' ? 'student_id' : 'teacher_id';
    }

    public static function columns(string $type): array
    {
        return $type === 'students'
            ? ['student_id','first_name','middle_name','last_name','grade_level','sex','school_year','email','status']
            : ['teacher_id','first_name','middle_name','last_name','email','subject','school_year','status'];
    }

    public static function rules(string $type): array
    {
        $rules = [
            self::key($type) => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'school_year' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ];
        if ($type === 'students') {
            $rules += ['grade_level' => 'nullable|string|max:255','sex' => 'required|in:Male,Female'];
        } else {
            $rules['subject'] = 'required|string|max:255';
        }
        return $rules;
    }

    public static function accountEmail(string $type, array $data): string
    {
        return trim($data['email'] ?? '') ?: ($data[self::key($type)] . '@forbes.edu');
    }

    public function linkedAccount(string $type, $record): ?User
    {
        if ($record->user_id) {
            return User::withTrashed()->find($record->user_id);
        }
        $email = $record->email ?: $record->{self::key($type)} . '@forbes.edu';
        return User::withTrashed()->where('email', $email)->where('role', substr($type, 0, -1))->first();
    }

    public function save(string $type, array $values, $record = null, string $source = 'form'): void
    {
        $model = self::model($type);
        $key = self::key($type);
        DB::transaction(function () use ($type, $values, $record, $model, $key, $source) {
            $data = array_intersect_key($values, array_flip(self::columns($type)));
            $data['middle_name'] = $data['middle_name'] ?? null;
            $data['email'] = $data['email'] ?? null;
            $email = self::accountEmail($type, $data);
            $name = trim($data['first_name'] . ' ' . $data['last_name']);
            $existingId = $model::withTrashed()->where($key, $data[$key])->first();
            if ($existingId && (!$record || $existingId->id !== $record->id)) {
                throw ValidationException::withMessages([$key => 'This ID already belongs to another record (including deleted records).']);
            }
            $account = $record ? $this->linkedAccount($type, $record) : null;
            if ($account && $account->role !== substr($type, 0, -1)) {
                throw ValidationException::withMessages(['email' => 'Linked account has the wrong role.']);
            }
            if ($account && $account->trashed()) {
                throw ValidationException::withMessages(['email' => 'This account has been deleted. Restore it first.']);
            }
            $occupied = User::withTrashed()->where('email', $email)->first();
            if ($occupied && (!$account || $occupied->id !== $account->id)) {
                throw ValidationException::withMessages(['email' => 'This email is already used by another account, including deleted accounts.']);
            }
            $before = $record ? $record->only(self::columns($type)) : null;
            if ($record) {
                $record->fill($data);
                $record->save();
                if ($account) {
                    $account->update(['name' => $name, 'email' => $email, 'is_active' => $data['status'] === 'active']);
                } elseif (!$record->user_id) {
                    // Repair an older student/teacher record that has no login account. Never
                    // replace a non-null broken foreign key or a deleted account silently.
                    $account = User::create([
                        'name' => $name, 'email' => $email,
                        'password' => Hash::make(Str::random(48)),
                        'role' => substr($type, 0, -1),
                        'is_active' => $data['status'] === 'active',
                    ]);
                    $account->update(['username' => substr($account->role, 0, 7) . '_' . $account->id]);
                    $record->update(['user_id' => $account->id]);
                    $this->audit->record('create', 'users', (string) $account->id, null,
                        $account->only(['name', 'email', 'username', 'role', 'is_active']),
                        ['source' => 'legacy_account_repair', 'requires_password_setup' => true]);
                } else {
                    throw ValidationException::withMessages(['email' => 'Linked login account is missing. Repair the broken association before editing.']);
                }
            } else {
                $account = User::create([
                    'name' => $name, 'email' => $email,
                    'password' => Hash::make(Str::random(48)),
                    'role' => substr($type, 0, -1),
                    'is_active' => $data['status'] === 'active',
                ]);
                $account->update(['username' => substr($account->role, 0, 7) . '_' . $account->id]);
                $data['user_id'] = $account->id;
                $record = $model::create($data);
            }
            if (!$record->user_id) $record->update(['user_id' => $account->id]);
            $after = $record->only(self::columns($type));
            if ($before !== $after) {
                $this->audit->record($before ? 'update' : 'create', $type, (string) $record->{$key}, $before, $after, ['source' => $source]);
            }
        });
    }

    public function changeStatus(string $type, int $id): string
    {
        $model = self::model($type);
        return DB::transaction(function () use ($type, $id, $model) {
            $record = $model::findOrFail($id);
            $before = $record->only(self::columns($type));
            $account = $this->linkedAccount($type, $record);
            if (!$account || $account->trashed() || $account->role !== substr($type, 0, -1)) {
                throw ValidationException::withMessages(['id' => 'The linked login account is unavailable.']);
            }
            $record->status = $record->status === 'active' ? 'inactive' : 'active';
            $record->save();
            $account->is_active = $record->status === 'active';
            $account->save();
            $this->audit->record('status_change', $type, (string) $record->{self::key($type)}, $before, $record->only(self::columns($type)));
            return $record->status;
        });
    }

    /** Set an explicit state rather than toggling each selection (idempotent). */
    public function setStatuses(string $type, array $ids, string $status): int
    {
        if (!in_array($status, ['active', 'inactive'], true)) {
            throw ValidationException::withMessages(['status' => 'Invalid account status.']);
        }
        $model = self::model($type);
        return DB::transaction(function () use ($type, $ids, $status, $model) {
            $records = $model::whereIn('id', $ids)->lockForUpdate()->get();
            if ($records->count() !== count($ids)) {
                throw ValidationException::withMessages(['ids' => 'One or more records no longer exist. Reload and try again.']);
            }
            $changed = 0;
            foreach ($records as $record) {
                if ($record->status === $status) continue;
                $before = $record->only(self::columns($type));
                $account = $this->linkedAccount($type, $record);
                if (!$account || $account->trashed() || $account->role !== substr($type, 0, -1)) {
                    throw ValidationException::withMessages(['ids' => 'A linked login account is unavailable or has the wrong role. No records were changed.']);
                }
                $record->status = $status;
                $record->save();
                $account->is_active = $status === 'active';
                $account->save();
                $this->audit->record('status_change', $type, (string) $record->{self::key($type)}, $before, $record->only(self::columns($type)), ['source' => 'bulk_status']);
                $changed++;
            }
            return $changed;
        });
    }

    public function delete(string $type, array $ids, string $source = 'form'): int
    {
        $model = self::model($type);
        return DB::transaction(function () use ($type, $ids, $model, $source) {
            $records = $model::whereIn('id', $ids)->lockForUpdate()->get();
            if ($records->count() !== count($ids)) {
                throw ValidationException::withMessages(['ids' => 'One or more records are missing or were already deleted. Reload and try again.']);
            }
            foreach ($records as $record) {
                $account = $this->linkedAccount($type, $record);
                if (!$account) throw ValidationException::withMessages(['ids' => 'An associated login account is missing. Nothing was deleted.']);
                if ($account->role !== substr($type, 0, -1)) throw ValidationException::withMessages(['ids' => 'Unexpected linked account role. Nothing was deleted.']);
                $before = $record->only(self::columns($type));
                $record->delete();
                $account->delete();
                $this->audit->record('delete', $type, (string) $record->{self::key($type)}, $before, null, ['source' => $source, 'recoverable' => true]);
            }
            return $records->count();
        });
    }

    public function restore(string $type, int $id): void
    {
        $model = self::model($type);
        DB::transaction(function () use ($type, $id, $model) {
            $record = $model::onlyTrashed()->findOrFail($id);
            $account = $this->linkedAccount($type, $record);
            if (!$account || !$account->trashed()) {
                throw ValidationException::withMessages(['id' => 'Linked account cannot be restored automatically.']);
            }
            $account->restore();
            $record->restore();
            $this->audit->record('restore', $type, (string) $record->{self::key($type)}, null, $record->only(self::columns($type)));
        });
    }
}
