<?php

namespace App\Services;

use App\Models\Faculty;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\AccountSetupToken;

class PersonRecordService
{
    public function __construct(
        private ActivityRecorder $audit,
        private AccountSetupService $accountSetup
    ) {}

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
        ];

        if ($type === 'students') {
            $rules += [
                'status' => 'required|in:active,inactive,pending',
                'grade_level' => 'required|string|max:255',
                'sex' => 'required|in:Male,Female',
                'school_year' => 'required|string|max:255',
                'email' => 'required|email|max:255',
            ];
        } elseif ($type === 'faculty') {
            $rules += [
                'email' => 'required|email|max:255',
            ];
        } else {
            throw new \InvalidArgumentException('Unsupported record type.');
        }

        return $rules;
    }

    public static function accountEmail(string $type, array $data): string
    {
        $email = trim($data['email'] ?? '');

        if ($email === '') {
            throw ValidationException::withMessages([
                'email' => 'A valid email address is required.',
            ]);
        }

        return $email;
    }

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

        // An imported Faculty may not have a role or User account yet.
        if ($type === 'faculty' && !$record->role) {
            return null;
        }

        $email = $record->email;

        if (!$email) {
            return null;
        }

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
        $setupUser = null;

        $isFacultyImport = $type === 'faculty' && $source === 'csv_import';

        DB::transaction(function () use (
            $type,
            $values,
            $record,
            $model,
            $key,
            $source,
            $isFacultyImport,
            &$setupUser
        ) {
            $data = $values;

            $data['middle_name'] = $data['middle_name'] ?? null;
            $data['email'] = trim($data['email'] ?? '');

            $email = self::accountEmail($type, $data);
            $name = trim($data['first_name'] . ' ' . $data['last_name']);

            if ($isFacultyImport) {
                unset($data['role'], $data['user_id'], $data['status']);

                if (!$record) {
                    $data['status'] = 'pending';
                }
            } else {
                self::accountRole(
                    $type,
                    $type === 'faculty' ? ($data['role'] ?? null) : null
                );
            }

            $existingId = $model::withTrashed()
                ->where($key, $data[$key])
                ->first();

            if ($existingId && (!$record || $existingId->id !== $record->id)) {
                throw ValidationException::withMessages([
                    $key => 'This ID already belongs to another record, including deleted records.',
                ]);
            }

            $account = $record
                ? $this->linkedAccount($type, $record)
                : null;

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
                    $account->save();
                } elseif ($type === 'students' && !$record->user_id) {
                    // Preserve the existing Student account repair behavior.
                    $newRole = self::accountRole('students');

                    $account = User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make(Str::random(48)),
                        'role' => $newRole,
                        'account_status' => 'pending',
                    ]);

                    $record->user_id = $account->id;
                    $record->save();

                    $setupUser = $account;
                } elseif (!$isFacultyImport && !$account && $record->user_id) {
                    throw ValidationException::withMessages([
                        'email' => 'The linked login account is missing. Repair the association before editing.',
                    ]);
                }
            } else {
                if ($isFacultyImport) {
                    // Create only the Faculty profile.
                    // No User account and no setup email at this stage.
                    $data['user_id'] = null;
                    $record = $model::create($data);
                } else {
                    // Preserve the existing Student account creation workflow.
                    $newRole = self::accountRole(
                        $type,
                        $type === 'faculty' ? ($data['role'] ?? null) : null
                    );

                    $account = User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make(Str::random(48)),
                        'role' => $newRole,
                        'account_status' => 'pending',
                    ]);

                    $data['user_id'] = $account->id;
                    $record = $model::create($data);

                    if ($type === 'students') {
                        $setupUser = $account;
                    }
                }
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

        // Only send setup links for the existing Student workflow here.
        // Faculty setup links will be sent after Admin completes assignments.
        if ($setupUser) {
            $this->accountSetup->sendSetupLink($setupUser);
        }
    }

    public function changeStatus(string $type, int $id): string
    {
        $model = self::model($type);

        return DB::transaction(function () use ($type, $id, $model) {
            $record = $model::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            $before = $record->only(self::columns($type));
            $account = $this->linkedAccount($type, $record);

            if ($type === 'faculty') {
                // Imported Faculty without completed assignments.
                if (!$record->user_id && !$record->role) {
                    $record->status = $record->status === 'inactive'
                        ? 'pending'
                        : 'inactive';

                    $record->save();
                } else {
                    if (
                        !$account ||
                        $account->trashed() ||
                        $account->role !== $record->role
                    ) {
                        throw ValidationException::withMessages([
                            'id' => 'The linked login account is unavailable or has the wrong role.',
                        ]);
                    }

                    $completedSetup = $this->hasCompletedSetup($account);

                    if ($record->status === 'active') {
                        $record->status = 'inactive';

                        if ($completedSetup) {
                            $account->update([
                                'account_status' => 'inactive',
                            ]);
                        }
                    } else {
                        if (!$completedSetup) {
                            $record->status = 'pending';
                            $account->update([
                                'account_status' => 'pending',
                            ]);
                        } else {
                            $record->status = 'active';
                            $account->update([
                                'account_status' => 'active',
                            ]);
                        }
                    }

                    $record->save();
                }
            } else {
                // Preserve the existing Student status behavior.
                $expectedRole = self::accountRole($type);

                if (
                    !$account ||
                    $account->trashed() ||
                    $account->role !== $expectedRole
                ) {
                    throw ValidationException::withMessages([
                        'id' => 'The linked login account is unavailable or has the wrong role.',
                    ]);
                }

                $record->status = $record->status === 'active'
                    ? 'inactive'
                    : 'active';

                $record->save();
            }

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

            /*
         * Prepare and validate all Faculty account states first.
         * This prevents partial updates if one record has an invalid account.
         */
            $facultyStates = [];

            if ($type === 'faculty') {
                foreach ($records as $record) {
                    // Imported Faculty without assignments or a login account.
                    if (!$record->user_id && !$record->role) {
                        $facultyStates[$record->id] = [
                            'faculty_status' => $status === 'active'
                                ? 'pending'
                                : 'inactive',
                            'account' => null,
                            'account_status' => null,
                        ];

                        continue;
                    }

                    $account = $this->linkedAccount($type, $record);

                    if (
                        !$account ||
                        $account->trashed() ||
                        $account->role !== $record->role
                    ) {
                        throw ValidationException::withMessages([
                            'ids' => 'A linked login account is unavailable or has the wrong role. No records were changed.',
                        ]);
                    }

                    $completedSetup = $this->hasCompletedSetup($account);

                    $facultyStatus = $status === 'inactive'
                        ? 'inactive'
                        : ($completedSetup ? 'active' : 'pending');

                    $accountStatus = $completedSetup
                        ? $facultyStatus
                        : 'pending';

                    $facultyStates[$record->id] = [
                        'faculty_status' => $facultyStatus,
                        'account' => $account,
                        'account_status' => $accountStatus,
                    ];
                }
            }

            $changed = 0;

            foreach ($records as $record) {
                $before = $record->only(self::columns($type));
                $recordChanged = false;

                if ($type === 'faculty') {
                    $state = $facultyStates[$record->id];

                    $newFacultyStatus = $state['faculty_status'];
                    $account = $state['account'];
                    $newAccountStatus = $state['account_status'];

                    if ($record->status !== $newFacultyStatus) {
                        $record->status = $newFacultyStatus;
                        $record->save();
                        $recordChanged = true;
                    }

                    if (
                        $account &&
                        $account->account_status !== $newAccountStatus
                    ) {
                        $account->update([
                            'account_status' => $newAccountStatus,
                        ]);

                        $recordChanged = true;
                    }
                } else {
                    // Preserve existing Student behavior.
                    if ($record->status === $status) {
                        continue;
                    }

                    $account = $this->linkedAccount($type, $record);
                    $expectedRole = self::accountRole($type);

                    if (
                        !$account ||
                        $account->trashed() ||
                        $account->role !== $expectedRole
                    ) {
                        throw ValidationException::withMessages([
                            'ids' => 'A linked login account is unavailable or has the wrong role. No records were changed.',
                        ]);
                    }

                    $record->status = $status;
                    $record->save();
                    $recordChanged = true;
                }

                if ($recordChanged) {
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

                $unassignedFaculty = $type === 'faculty'
                    && !$record->user_id
                    && !$record->role;

                if (!$unassignedFaculty) {
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
                }

                $before = $record->only(self::columns($type));

                $record->delete();

                if ($account) {
                    $account->delete();
                }

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

            $unassignedFaculty = $type === 'faculty'
                && !$record->user_id
                && !$record->role;

            if (!$unassignedFaculty) {
                $expectedRole = self::accountRole(
                    $type,
                    $type === 'faculty' ? $record->role : null
                );

                if (
                    !$account ||
                    !$account->trashed() ||
                    $account->role !== $expectedRole
                ) {
                    throw ValidationException::withMessages([
                        'id' => 'The linked account cannot be restored automatically.',
                    ]);
                }

                $account->restore();
            }

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

    private function hasCompletedSetup(User $user): bool
    {
        return AccountSetupToken::where('user_id', $user->id)
            ->whereNotNull('used_at')
            ->exists();
    }
}
