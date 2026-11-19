<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use App\Models\User;
use App\Models\AdvisoryAssignment;
use App\Services\PersonRecordService;
use App\Services\SchoolCsvSnapshotService;
use App\Services\AccountSetupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FacultyController extends Controller
{
    public function index()
    {
        $faculty = Faculty::with([
            'teachingAssignments',
            'advisoryAssignments',
        ])->latest()->get();

        return view('admin.faculty-management', compact('faculty'));
    }

    /**
     * Create a Faculty manually.
     * Both Faculty and User start as pending.
     */
    public function store(Request $request, AccountSetupService $accountSetup)
    {
        $validated = $request->validate([
            'faculty_id' => [
                'required',
                'string',
                'max:50',
                'unique:faculty,faculty_id',
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:faculty,email',
                'unique:users,email',
            ],
            'teacher' => ['required', 'boolean'],
            'adviser' => ['required', 'boolean'],
            'assignments' => ['nullable', 'array'],
            'assignments.*.grade_level' => [
                'required',
                'string',
                'max:50',
            ],
            'assignments.*.subject' => [
                'required',
                'string',
                'max:255',
            ],
            'advisory_grade' => ['nullable', 'string', 'max:50'],
            'school_year' => ['required', 'string', 'max:20'],
        ]);

        if (!$validated['teacher'] && !$validated['adviser']) {
            return response()->json([
                'message' => 'Please select Teacher, Adviser, or both.',
            ], 422);
        }

        if ($validated['adviser'] && empty($validated['advisory_grade'])) {
            return response()->json([
                'message' => 'Please select an advisory grade level.',
            ], 422);
        }

        if ($validated['teacher'] && empty($validated['assignments'])) {
            return response()->json([
                'message' => 'Please add at least one teaching assignment.',
            ], 422);
        }

        $role = $validated['teacher'] && $validated['adviser']
            ? 'teacher_adviser'
            : ($validated['adviser'] ? 'adviser' : 'teacher');

        $faculty = DB::transaction(function () use ($validated, $role) {

            if ($validated['adviser']) {
                $alreadyAssigned = AdvisoryAssignment::where(
                    'grade_level',
                    $validated['advisory_grade']
                )->exists();

                if ($alreadyAssigned) {
                    throw ValidationException::withMessages([
                        'advisory_grade' =>
                        "An adviser is already assigned to {$validated['advisory_grade']}. Only one adviser is allowed per grade level.",
                    ]);
                }
            }

            $name = trim(
                $validated['first_name'] . ' ' .
                    ($validated['middle_name'] ?? '') . ' ' .
                    $validated['last_name']
            );

            $user = User::create([
                'name' => $name,
                'email' => $validated['email'],
                'password' => Str::random(64),
                'role' => $role,
                'account_status' => 'pending',
            ]);

            $faculty = Faculty::create([
                'user_id' => $user->id,
                'faculty_id' => $validated['faculty_id'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'status' => 'pending',
                'role' => $role,
            ]);

            if ($validated['teacher']) {
                foreach ($validated['assignments'] as $assignment) {
                    $faculty->teachingAssignments()->create([
                        'grade_level' => $assignment['grade_level'],
                        'subject' => $assignment['subject'],
                        'school_year' => $validated['school_year'],
                    ]);
                }
            }

            if ($validated['adviser']) {
                $faculty->advisoryAssignments()->create([
                    'grade_level' => $validated['advisory_grade'],
                    'school_year' => $validated['school_year'],
                ]);
            }

            return $faculty;
        });

        try {
            $accountSetup->sendSetupLink($faculty->user);
            $emailSent = true;
        } catch (\Throwable $e) {
            report($e);
            $emailSent = false;
        }

        return response()->json([
            'message' => $emailSent
                ? 'Faculty created successfully. A password setup link has been sent to the faculty email.'
                : 'Faculty created successfully, but the password setup email could not be sent. Please resend the setup link.',
            'email_sent' => $emailSent,
            'faculty' => $faculty->load([
                'teachingAssignments',
                'advisoryAssignments',
            ]),
        ], 201);
    }

    /**
     * Update Faculty details and assignments.
     * Imported Faculty receives a User account only after assignments
     * have been validated and saved successfully.
     */
    public function update(
        Request $request,
        int $id,
        AccountSetupService $accountSetup
    ) {
        $faculty = Faculty::findOrFail($id);

        $validated = $request->validate([
            'faculty_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('faculty', 'faculty_id')->ignore($faculty->id),
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('faculty', 'email')->ignore($faculty->id),
                Rule::unique('users', 'email')->ignore($faculty->user_id),
            ],
            'teacher' => ['required', 'boolean'],
            'adviser' => ['required', 'boolean'],
            'assignments' => ['nullable', 'array'],
            'assignments.*.grade_level' => [
                'required',
                'string',
                'max:50',
            ],
            'assignments.*.subject' => [
                'required',
                'string',
                'max:255',
            ],
            'advisory_grade' => ['nullable', 'string', 'max:50'],
            'school_year' => ['required', 'string', 'max:20'],
        ]);

        if (!$validated['teacher'] && !$validated['adviser']) {
            return response()->json([
                'message' => 'Please select Teacher, Adviser, or both.',
            ], 422);
        }

        if ($validated['adviser'] && empty($validated['advisory_grade'])) {
            return response()->json([
                'message' => 'Please select an advisory grade level.',
            ], 422);
        }

        if ($validated['teacher'] && empty($validated['assignments'])) {
            return response()->json([
                'message' => 'Please add at least one teaching assignment.',
            ], 422);
        }

        $role = $validated['teacher'] && $validated['adviser']
            ? 'teacher_adviser'
            : ($validated['adviser'] ? 'adviser' : 'teacher');

        $setupUser = null;

        $faculty = DB::transaction(function () use (
            $id,
            $validated,
            $role,
            &$setupUser
        ) {
            $faculty = Faculty::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            $isUnassignedImport = !$faculty->user_id && !$faculty->role;

            $account = $faculty->user_id
                ? User::withTrashed()->find($faculty->user_id)
                : null;

            if (!$isUnassignedImport) {
                if (
                    !$account ||
                    $account->trashed()
                ) {
                    throw ValidationException::withMessages([
                        'email' => 'The linked login account is unavailable. Repair or restore the account before editing this Faculty.',
                    ]);
                }
            }

            if ($validated['adviser']) {
                $alreadyAssigned = AdvisoryAssignment::where(
                    'grade_level',
                    $validated['advisory_grade']
                )
                    ->where('faculty_id', '!=', $faculty->id)
                    ->exists();

                if ($alreadyAssigned) {
                    throw ValidationException::withMessages([
                        'advisory_grade' =>
                        "An adviser is already assigned to {$validated['advisory_grade']}. Only one adviser is allowed per grade level.",
                    ]);
                }
            }

            $name = trim(
                $validated['first_name'] . ' ' .
                    ($validated['middle_name'] ?? '') . ' ' .
                    $validated['last_name']
            );

            $faculty->update([
                'faculty_id' => $validated['faculty_id'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'role' => $role,
            ]);

            if ($account) {
                // Keep the existing password and account status.
                $account->update([
                    'name' => $name,
                    'email' => $validated['email'],
                    'role' => $role,
                ]);
            }

            // Replace the previous assignments.
            $faculty->teachingAssignments()->delete();
            $faculty->advisoryAssignments()->delete();

            if ($validated['teacher']) {
                foreach ($validated['assignments'] as $assignment) {
                    $faculty->teachingAssignments()->create([
                        'grade_level' => $assignment['grade_level'],
                        'subject' => $assignment['subject'],
                        'school_year' => $validated['school_year'],
                    ]);
                }
            }

            if ($validated['adviser']) {
                $faculty->advisoryAssignments()->create([
                    'grade_level' => $validated['advisory_grade'],
                    'school_year' => $validated['school_year'],
                ]);
            }

            // Create the login account only for an imported,
            // unassigned Faculty record.
            if ($isUnassignedImport) {
                $setupUser = User::create([
                    'name' => $name,
                    'email' => $validated['email'],
                    'password' => Str::random(64),
                    'role' => $role,
                    'account_status' => 'pending',
                ]);

                $faculty->update([
                    'user_id' => $setupUser->id,
                    'status' => 'pending',
                ]);
            }

            return $faculty->fresh()->load([
                'teachingAssignments',
                'advisoryAssignments',
            ]);
        });

        $emailSent = null;

        if ($setupUser) {
            try {
                $accountSetup->sendSetupLink($setupUser);
                $emailSent = true;
            } catch (\Throwable $e) {
                report($e);
                $emailSent = false;
            }
        }

        $message = 'Faculty updated successfully.';

        if ($setupUser) {
            $message = $emailSent
                ? 'Faculty assignments saved successfully. A login account was created and a password setup link has been sent.'
                : 'Faculty assignments saved and login account created, but the password setup email could not be sent. Please resend the setup link.';
        }

        return response()->json([
            'message' => $message,
            'email_sent' => $emailSent,
            'faculty' => $faculty,
        ]);
    }

    public function toggleStatus(
        int $id,
        PersonRecordService $records
    ) {
        $status = $records->changeStatus('faculty', $id);

        return response()->json([
            'success' => true,
            'status' => $status,
            'message' => $status === 'active'
                ? 'Faculty account has been reactivated successfully.'
                : ($status === 'inactive'
                    ? 'Faculty account has been deactivated successfully.'
                    : 'Faculty account is still pending password setup.'),
        ]);
    }

    public function destroy(
        PersonRecordService $records,
        SchoolCsvSnapshotService $snapshots,
        int $id
    ) {
        $records->delete('faculty', [$id]);

        $snapshots->refresh('faculty');

        return response()->json([
            'message' => 'Faculty deleted successfully.',
        ]);
    }

    public function bulkStatus(
        Request $request,
        PersonRecordService $records,
        SchoolCsvSnapshotService $snapshots
    ) {
        $data = $request->validate([
            'ids' => 'required|array|min:1|max:500',
            'ids.*' => 'required|integer|distinct|min:1',
            'status' => 'required|in:active,inactive',
        ]);

        $count = $records->setStatuses(
            'faculty',
            array_map('intval', $data['ids']),
            $data['status']
        );

        $snapshots->refresh('faculty');

        return response()->json([
            'success' => true,
            'message' => "$count faculty updated to {$data['status']}.",
            'changed' => $count,
        ]);
    }
}
