<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use App\Models\User;
use App\Models\AdvisoryAssignment;
use App\Services\PersonRecordService;
use App\Services\SchoolCsvSnapshotService;
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

    public function store(Request $request)
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
            'status' => ['required', Rule::in(['active', 'inactive'])],
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
            'school_year' => ['nullable', 'string', 'max:20'],
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

            // Enforce one adviser per grade level.
            if ($validated['adviser']) {
                $grade = $validated['advisory_grade'];

                $alreadyAssigned = AdvisoryAssignment::where(
                    'grade_level',
                    $grade
                )->exists();

                if ($alreadyAssigned) {
                    throw ValidationException::withMessages([
                        'advisory_grade' =>
                            "An adviser is already assigned to {$grade}. Only one adviser is allowed per grade level.",
                    ]);
                }
            }

            // Create a pending login account with a random initial password.
            // The User model's hashed password cast will hash this value.
            $user = User::create([
                'name' => trim(
                    $validated['first_name'] . ' ' .
                    ($validated['middle_name'] ?? '') . ' ' .
                    $validated['last_name']
                ),
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
                'status' => $validated['status'],
                'role' => $role,
            ]);

            if ($validated['teacher']) {
                foreach ($validated['assignments'] ?? [] as $assignment) {
                    $faculty->teachingAssignments()->create([
                        'grade_level' => $assignment['grade_level'],
                        'subject' => $assignment['subject'],
                    ]);
                }
            }

            if ($validated['adviser']) {
                $faculty->advisoryAssignments()->create([
                    'grade_level' => $validated['advisory_grade'],
                    'school_year' => $validated['school_year'] ?? null,
                ]);
            }

            return $faculty;
        });

        return response()->json([
            'message' => 'Faculty and pending login account created successfully.',
            'faculty' => $faculty->load([
                'teachingAssignments',
                'advisoryAssignments',
            ]),
        ], 201);
    }

    public function update(Request $request, int $id)
    {
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
            'status' => ['required', Rule::in(['active', 'inactive'])],
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
            'school_year' => ['nullable', 'string', 'max:20'],
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

        DB::transaction(function () use ($faculty, $validated, $role) {

            // Check whether another faculty member already advises this grade.
            if ($validated['adviser']) {
                $grade = $validated['advisory_grade'];

                $alreadyAssigned = AdvisoryAssignment::where(
                    'grade_level',
                    $grade
                )
                    ->where('faculty_id', '!=', $faculty->id)
                    ->exists();

                if ($alreadyAssigned) {
                    throw ValidationException::withMessages([
                        'advisory_grade' =>
                            "An adviser is already assigned to {$grade}. Only one adviser is allowed per grade level.",
                    ]);
                }
            }

            $faculty->update([
                'faculty_id' => $validated['faculty_id'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'status' => $validated['status'],
                'role' => $role,
            ]);

            // Update the linked login account without resetting its password
            // or changing its account activation status.
            if ($faculty->user) {
                $faculty->user->update([
                    'name' => trim(
                        $validated['first_name'] . ' ' .
                        ($validated['middle_name'] ?? '') . ' ' .
                        $validated['last_name']
                    ),
                    'email' => $validated['email'],
                    'role' => $role,
                ]);
            }

            $faculty->teachingAssignments()->delete();
            $faculty->advisoryAssignments()->delete();

            if ($validated['teacher']) {
                foreach ($validated['assignments'] ?? [] as $assignment) {
                    $faculty->teachingAssignments()->create([
                        'grade_level' => $assignment['grade_level'],
                        'subject' => $assignment['subject'],
                    ]);
                }
            }

            if ($validated['adviser']) {
                $faculty->advisoryAssignments()->create([
                    'grade_level' => $validated['advisory_grade'],
                    'school_year' => $validated['school_year'] ?? null,
                ]);
            }
        });

        return response()->json([
            'message' => 'Faculty updated successfully.',
            'faculty' => $faculty->fresh()->load([
                'teachingAssignments',
                'advisoryAssignments',
            ]),
        ]);
    }

    public function toggleStatus(int $id)
    {
        $faculty = DB::transaction(function () use ($id) {

            $faculty = Faculty::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            $faculty->status = strtolower($faculty->status) === 'active'
                ? 'inactive'
                : 'active';

            $faculty->save();

            return $faculty;
        });

        return response()->json([
            'success' => true,
            'status' => $faculty->status,
            'message' => $faculty->status === 'active'
                ? 'Faculty account has been reactivated successfully.'
                : 'Faculty account has been deactivated successfully.',
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