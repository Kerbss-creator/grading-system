<?php

namespace App\Services;

use App\Models\Faculty;
use App\Models\Student;
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
            'faculty' => Faculty::class,
            default => throw new \InvalidArgumentException('Unsupported record type.'),
        };
    }

    public static function key(string $type): string
    {
        return match ($type) {
            'students' => 'student_id',
            'faculty' => 'faculty_id',
            default => throw new \InvalidArgumentException('Unsupported record type.'),
        };
    }

    public static function columns(string $type): array
    {
        return match ($type) {
            'students' => [
                'student_id',
                'first_name',
                'middle_name',
                'last_name',
                'grade_level',
                'sex',
                'school_year',
                'email',
                'status',
            ],
            'faculty' => [
                'faculty_id',
                'first_name',
                'middle_name',
                'last_name',
                'email',
                'role',
                'status',
            ],
            default => throw new \InvalidArgumentException('Unsupported record type.'),
        };
    }

    public static function rules(string $type): array
    {
        $rules = [
            self::key($type) => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ];

        if ($type === 'students') {
            $rules += [
                'grade_level' => 'required|string|max:255',
                'sex' => 'required|in:Male,Female',
                'school_year' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
            ];
        } elseif ($type === 'faculty') {
            $rules += [
                'email' => 'required|email|max:255',
                'role' => 'required|in:teacher,adviser,teacher_adviser',
            ];
        } else {
            throw new \InvalidArgumentException('Unsupported record type.');
        }

        return $rules;
    }

    /**
     * Student accounts use a generated email if none is supplied.
     * Faculty accounts must use the faculty email.
     */
    public static function accountEmail(string $type, array $data): string
    {
        $email = trim($data['email'] ?? '');

        if ($email !== '') {
            return $email;
        }

        if ($type === 'students') {
            return $data['student_id'] . '@forbes.edu';
        }

        throw new \InvalidArgumentException('Faculty email is required.');
    }

    /**
     * Returns the login role associated with a record.
     */
    public static function accountRole(string $type, ?string $facultyRole = null): string
    {
        if ($type === 'students') {
            return 'student';
        }

        if (
            $type === 'faculty' &&
            in_array($facultyRole, ['teacher', 'adviser', 'teacher_adviser'], true)
        ) {
            return $facultyRole;
        }

        throw new \InvalidArgumentException('Invalid account role.');
    }

    public function linkedAccount(string $type, $record): ?User
    {
        if ($record->user_id) {
            return User::withTrashed()->find($record->user_id);
        }

        $email = $record->email ?: $record->{self::key($type)} . '@forbes.edu';

        $role = self::accountRole(
            $type,
            $type === 'faculty' ? $record->role : null
        );

        return User::withTrashed()
            ->where('email', $email)
            ->where('role', $role)
            ->first();
    }

    public function save(
        string $type,
        array $values,
        $record = null,
        string $source = 'form'
    ): void {
        $model = self::model($type);
        $key = self::key($type);

        DB::transaction(function () use ($type, $values, $record, $model, $key, $source) {
            $data = array_intersect_key(
                $values,
                array_flip(self::columns($type))
            );

            $data['middle_name'] = $data['middle_name'] ?? null;
            $data['email'] = $data['email'] ?? null;

            $email = self::accountEmail($type, $data);
            $newRole = self::accountRole(
                $type,
                $type === 'faculty' ? ($data['role'] ?? null) : null
            );

            $name = trim($data['first_name'] . ' ' . $data['last_name']);

            $existingId = $model::withTrashed()
                ->where($key, $data[$key])
                ->first();

            if ($existingId && (!$record || $existingId->id !== $record->id)) {
                throw ValidationException::withMessages([
                    $key => 'This ID already belongs to another record, including deleted records.',
                ]);
            }

            $account = $record ? $this->linkedAccount($type, $record) : null;

            // Check the account against the record's CURRENT role before changing it.
            if ($record && $account) {
                $currentRole = self::accountRole(
                    $type,
                    $type === 'faculty' ? $record->role : null
                );

                if ($account->role !== $currentRole) {
                    throw ValidationException::withMessages([
                        'email' => 'The linked account role does not match the existing record.',
                    ]);
                }
            }

            if ($account && $account->trashed()) {
                throw ValidationException::withMessages([
                    'email' => 'This linked account has been deleted. Restore it first.',
                ]);
            }

            $occupied = User::withTrashed()
                ->where('email', $email)
                ->first();

            if ($occupied && (!$account || $occupied->id !== $account->id)) {
                throw ValidationException::withMessages([
                    'email' => 'This email is already used by another account, including deleted accounts.',
                ]);
            }

            $before = $record
                ? $record->only(self::columns($type))
                : null;

            if ($record) {
                $record->fill($data);
                $record->save();

                if ($account) {
                    $account->name = $name;
                    $account->email = $email;
                    $account->role = $newRole;
                    $account->save();
                } elseif (!$record->user_id) {
                    // Repair an older record that has no linked login account.
                    $account = User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make(Str::random(48)),
                        'role' => $newRole,
                    ]);

                    $record->user_id = $account->id;
                    $record->save();

                    $this->audit->record(
                        'create',
                        'users',
                        (string) $account->id,
                        null,
                        $account->only(['name', 'email', 'role']),
                        [
                            'source' => 'legacy_account_repair',
                            'requires_password_setup' => true,
                        ]
                    );
                } else {
                    throw ValidationException::withMessages([
                        'email' => 'The linked login account is missing. Repair the association before editing.',
                    ]);
                }
            } else {
                // Create the linked login account first.
                $account = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make(Str::random(48)),
                    'role' => $newRole,
                ]);

                $data['user_id'] = $account->id;
                $record = $model::create($data);
            }

            if (!$record->user_id) {
                $record->user_id = $account->id;
                $record->save();
            }

            $after = $record->only(self::columns($type));

            if ($before !== $after) {
                $this->audit->record(
                    $before ? 'update' : 'create',
                    $type,
                    (string) $record->{$key},
                    $before,
                    $after,
                    ['source' => $source]
                );
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

            $expectedRole = self::accountRole(
                $type,
                $type === 'faculty' ? $record->role : null
            );

            if (!$account || $account->trashed() || $account->role !== $expectedRole) {
                throw ValidationException::withMessages([
                    'id' => 'The linked login account is unavailable or has the wrong role.',
                ]);
            }

            $record->status = $record->status === 'active' ? 'inactive' : 'active';
            $record->save();

            $this->audit->record(
                'status_change',
                $type,
                (string) $record->{self::key($type)},
                $before,
                $record->only(self::columns($type))
            );

            return $record->status;
        });
    }

    public function setStatuses(string $type, array $ids, string $status): int
    {
        if (!in_array($status, ['active', 'inactive'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Invalid account status.',
            ]);
        }

        $model = self::model($type);

        return DB::transaction(function () use ($type, $ids, $status, $model) {
            $records = $model::whereIn('id', $ids)
                ->lockForUpdate()
                ->get();

            if ($records->count() !== count($ids)) {
                throw ValidationException::withMessages([
                    'ids' => 'One or more records no longer exist. Reload and try again.',
                ]);
            }

            $changed = 0;

            foreach ($records as $record) {
                if ($record->status === $status) {
                    continue;
                }

                $before = $record->only(self::columns($type));
                $account = $this->linkedAccount($type, $record);

                $expectedRole = self::accountRole(
                    $type,
                    $type === 'faculty' ? $record->role : null
                );

                if (!$account || $account->trashed() || $account->role !== $expectedRole) {
                    throw ValidationException::withMessages([
                        'ids' => 'A linked login account is unavailable or has the wrong role. No records were changed.',
                    ]);
                }

                $record->status = $status;
                $record->save();

                $this->audit->record(
                    'status_change',
                    $type,
                    (string) $record->{self::key($type)},
                    $before,
                    $record->only(self::columns($type)),
                    ['source' => 'bulk_status']
                );

                $changed++;
            }

            return $changed;
        });
    }

    public function delete(string $type, array $ids, string $source = 'form'): int
    {
        $model = self::model($type);

        return DB::transaction(function () use ($type, $ids, $model, $source) {
            $records = $model::whereIn('id', $ids)
                ->lockForUpdate()
                ->get();

            if ($records->count() !== count($ids)) {
                throw ValidationException::withMessages([
                    'ids' => 'One or more records are missing or were already deleted.',
                ]);
            }

            foreach ($records as $record) {
                $account = $this->linkedAccount($type, $record);

                $expectedRole = self::accountRole(
                    $type,
                    $type === 'faculty' ? $record->role : null
                );

                if (!$account) {
                    throw ValidationException::withMessages([
                        'ids' => 'An associated login account is missing. Nothing was deleted.',
                    ]);
                }

                if ($account->role !== $expectedRole) {
                    throw ValidationException::withMessages([
                        'ids' => 'Unexpected linked account role. Nothing was deleted.',
                    ]);
                }

                $before = $record->only(self::columns($type));

                $record->delete();
                $account->delete();

                $this->audit->record(
                    'delete',
                    $type,
                    (string) $record->{self::key($type)},
                    $before,
                    null,
                    ['source' => $source, 'recoverable' => true]
                );
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

            $expectedRole = self::accountRole(
                $type,
                $type === 'faculty' ? $record->role : null
            );

            if (!$account || !$account->trashed() || $account->role !== $expectedRole) {
                throw ValidationException::withMessages([
                    'id' => 'The linked account cannot be restored automatically.',
                ]);
            }

            $account->restore();
            $record->restore();

            $this->audit->record(
                'restore',
                $type,
                (string) $record->{self::key($type)},
                null,
                $record->only(self::columns($type))
            );
        });
    }
}
