<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use App\Models\User;
use App\Services\PersonRecordService;
use App\Services\SchoolCsvSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
            'faculty_id' => ['required', 'string', 'max:50', 'unique:faculty,faculty_id'],
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
            'temporary_password' => ['nullable', 'string', 'min:8', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'teacher' => ['required', 'boolean'],
            'adviser' => ['required', 'boolean'],
            'assignments' => ['nullable', 'array'],
            'assignments.*.grade_level' => ['required', 'string', 'max:50'],
            'assignments.*.subject' => ['required', 'string', 'max:255'],
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
                'message' => 'Please add at least one teaching assignment',
            ], 422);
        }

        // Determine login role based on faculty assignments.
        $role = $validated['teacher'] && $validated['adviser']
            ? 'teacher_adviser'
            : ($validated['adviser'] ? 'adviser' : 'teacher');

        $faculty = DB::transaction(function () use ($validated, $role) {

            // Create login account.
            $user = User::create([
                'name' => trim(
                    $validated['first_name'] . ' ' .
                    $validated['last_name']
                ),
                'email' => $validated['email'],
                'password' => Hash::make(
                    $validated['temporary_password'] ?? 'faculty123'
                ),
                'role' => $role,
            ]);

            // Link faculty profile to login account.
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
            'message' => 'Faculty and login account created successfully.',
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
            'assignments.*.grade_level' => ['required', 'string', 'max:50'],
            'assignments.*.subject' => ['required', 'string', 'max:255'],
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

        // Determine login role before the transaction.
        $role = $validated['teacher'] && $validated['adviser']
            ? 'teacher_adviser'
            : ($validated['adviser'] ? 'adviser' : 'teacher');

        DB::transaction(function () use ($faculty, $validated, $role) {

            $faculty->update([
                'faculty_id' => $validated['faculty_id'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'status' => $validated['status'],
                'role' => $role,
            ]);

            // Update linked login account.
            if ($faculty->user) {
                $faculty->user->update([
                    'name' => trim(
                        $validated['first_name'] . ' ' .
                        $validated['last_name']
                    ),
                    'email' => $validated['email'],
                    'role' => $role,
                ]);
            }

            // Replace existing assignments.
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

    public function destroy(
        PersonRecordService $records,
        SchoolCsvSnapshotService $snapshots,
        int $id
    ) {
        // Soft-delete faculty record and linked login account.
        $records->delete('faculty', [$id]);

        // Refresh faculty CSV snapshot.
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
            'message' => "$count faculty updated to " . $data['status'] . ".",
            'changed' => $count,
        ]);
    }

    public function toggleStatus($id)
    {
        $faculty = Faculty::findOrFail($id);

        $faculty->status = $faculty->status === 'active'
            ? 'inactive'
            : 'active';

        $faculty->save();

        return response()->json([
            'success' => true,
            'message' => 'Faculty status updated successfully.',
            'status' => $faculty->status,
        ]);
    }
}
