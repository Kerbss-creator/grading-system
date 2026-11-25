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
    public function __construct(
        private ActivityRecorder $audit
    ) {
    }

    public static function columns(): array
    {
        return ['admin_id', 'name', 'email'];
    }

    public static function rules(): array
    {
        return [
            'admin_id' => 'nullable|integer|min:1',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ];
    }

    public function matched(array $data): ?User
    {
        if (!empty($data['admin_id'])) {
            return User::withTrashed()
                ->where('role', 'admin')
                ->find((int) $data['admin_id']);
        }

        return User::withTrashed()
            ->where('role', 'admin')
            ->where('email', $data['email'])
            ->first();
    }

    public function save(
        array $data,
        ?User $existing = null,
        string $source = 'form',
        ?string $password = null
    ): User {
        return DB::transaction(function () use ($data, $existing, $source, $password) {
            if (
                ($data['name'] ?? null) === 'ghostman' ||
                ($data['email'] ?? null) === SystemOwner::EMAIL
            ) {
                throw ValidationException::withMessages([
                    'name' => 'This account identity is reserved.',
                ]);
            }

            if ($existing) {
                if (
                    $existing->role !== 'admin' ||
                    $existing->trashed()
                ) {
                    throw ValidationException::withMessages([
                        'email' => 'This record is not an active administrator account.',
                    ]);
                }

                if (
                    $existing->name === 'ghostman' ||
                    SystemOwner::is($existing)
                ) {
                    throw ValidationException::withMessages([
                        'name' => 'This protected administrator account cannot be edited.',
                    ]);
                }
            }

            $conflict = User::withTrashed()
                ->where('email', $data['email'])
                ->first();

            if (
                $conflict &&
                (!$existing || (int) $conflict->id !== (int) $existing->id)
            ) {
                throw ValidationException::withMessages([
                    'email' => 'Email is already assigned to a user account, including deleted accounts.',
                ]);
            }

            $before = $existing?->only([
                'name',
                'email',
                'role',
                'account_status',
            ]);

            if ($existing) {
                $existing->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                ]);

                $user = $existing;
            } else {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'role' => 'admin',
                    'account_status' => 'active',
                    'password' => Hash::make(
                        $password ?? Str::random(64)
                    ),
                ]);
            }

            $after = $user->only([
                'name',
                'email',
                'role',
                'account_status',
            ]);

            if ($before !== $after) {
                $this->audit->record(
                    $before ? 'update' : 'create',
                    'admins',
                    (string) $user->id,
                    $before,
                    $after,
                    [
                        'source' => $source,
                        'requires_password_setup' => !$existing && !$password,
                    ]
                );
            }

            return $user;
        });
    }

    /**
     * Bulk activate/deactivate administrators.
     * Protects the current account, System Owner, and last active Admin.
     */
    public function setStatuses(array $ids, string $status): int
    {
        if (!in_array($status, ['active', 'inactive'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Invalid account status.',
            ]);
        }

        $ids = array_values(array_unique(array_map('intval', $ids)));

        if (empty($ids)) {
            throw ValidationException::withMessages([
                'ids' => 'Select at least one administrator.',
            ]);
        }

        return DB::transaction(function () use ($ids, $status) {
            $users = User::where('role', 'admin')
                ->whereIn('id', $ids)
                ->lockForUpdate()
                ->get();

            if ($users->count() !== count($ids)) {
                throw ValidationException::withMessages([
                    'ids' => 'One or more administrator accounts were not found.',
                ]);
            }

            if (
                $users->contains(function (User $user) {
                    return (int) $user->id === (int) auth()->id()
                        || SystemOwner::is($user);
                })
            ) {
                throw ValidationException::withMessages([
                    'ids' => 'Your own account and protected accounts cannot be changed in bulk.',
                ]);
            }

            if ($status === 'inactive') {
                $currentlyActive = User::where('role', 'admin')
                    ->where('account_status', 'active')
                    ->count();

                $toDeactivate = $users
                    ->where('account_status', 'active')
                    ->count();

                if ($currentlyActive <= $toDeactivate) {
                    throw ValidationException::withMessages([
                        'ids' => 'At least one active administrator must remain.',
                    ]);
                }
            }

            $changed = 0;

            foreach ($users as $user) {
                if ($user->account_status === $status) {
                    continue;
                }

                $before = $user->only([
                    'name',
                    'email',
                    'role',
                    'account_status',
                ]);

                $user->account_status = $status;
                $user->save();

                $this->audit->record(
                    'status_change',
                    'admins',
                    (string) $user->id,
                    $before,
                    $user->only([
                        'name',
                        'email',
                        'role',
                        'account_status',
                    ]),
                    ['source' => 'bulk_status']
                );

                $changed++;
            }

            return $changed;
        });
    }

    /**
     * Soft-delete selected administrator accounts.
     */
    public function delete(array $ids): int
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if (empty($ids)) {
            throw ValidationException::withMessages([
                'ids' => 'Select at least one administrator.',
            ]);
        }

        return DB::transaction(function () use ($ids) {
            $users = User::where('role', 'admin')
                ->whereIn('id', $ids)
                ->lockForUpdate()
                ->get();

            if ($users->count() !== count($ids)) {
                throw ValidationException::withMessages([
                    'ids' => 'One or more administrators do not exist.',
                ]);
            }

            if (
                $users->contains(function (User $user) {
                    return (int) $user->id === (int) auth()->id()
                        || $user->name === 'ghostman'
                        || SystemOwner::is($user);
                })
            ) {
                throw ValidationException::withMessages([
                    'ids' => 'You cannot delete your own account or a protected administrator account.',
                ]);
            }

            $currentlyActive = User::where('role', 'admin')
                ->where('account_status', 'active')
                ->count();

            $selectedActive = $users
                ->where('account_status', 'active')
                ->count();

            if ($currentlyActive <= $selectedActive) {
                throw ValidationException::withMessages([
                    'ids' => 'At least one active administrator must remain.',
                ]);
            }

            foreach ($users as $user) {
                $before = $user->only([
                    'name',
                    'email',
                    'role',
                    'account_status',
                ]);

                $user->delete();

                $this->audit->record(
                    'delete',
                    'admins',
                    (string) $user->id,
                    $before,
                    null,
                    ['recoverable' => true]
                );
            }

            return $users->count();
        });
    }

    /**
     * Restore a soft-deleted administrator.
     */
    public function restore(int $id): void
    {
        DB::transaction(function () use ($id) {
            $user = User::onlyTrashed()
                ->where('role', 'admin')
                ->lockForUpdate()
                ->findOrFail($id);

            if (SystemOwner::is($user)) {
                throw ValidationException::withMessages([
                    'admin' => 'The protected system owner account cannot be restored through this action.',
                ]);
            }

            $user->restore();

            $this->audit->record(
                'restore',
                'admins',
                (string) $user->id,
                null,
                $user->only([
                    'name',
                    'email',
                    'role',
                    'account_status',
                ])
            );
        });
    }

    /**
     * Reset an account password.
     */
    public function resetPassword(
        User $account,
        string $password
    ): void {
        DB::transaction(function () use ($account, $password) {
            if ($account->trashed()) {
                throw ValidationException::withMessages([
                    'password' => 'Restore the account before changing its password.',
                ]);
            }

            $before = [
                'account_status' => $account->account_status,
                'role' => $account->role,
            ];

            $account->update([
                'password' => Hash::make($password),
            ]);

            $this->audit->record(
                'password_reset',
                'users',
                (string) $account->id,
                $before,
                null,
                [
                    'account_email' => $account->email,
                    'role' => $account->role,
                ]
            );
        });
    }
}
