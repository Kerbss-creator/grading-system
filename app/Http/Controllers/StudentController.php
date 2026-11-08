<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Services\PersonRecordService;
use App\Services\SchoolCsvSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.student-management', compact('students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => [
                'required',
                'string',
                'max:255',
                'unique:students,student_id',
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'grade_level' => ['required', 'string', 'max:255'],
            'sex' => ['required', 'string', 'max:255'],
            'school_year' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:students,email',
                'unique:users,email',
            ],
            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
        ]);

        DB::transaction(function () use ($validated) {
            $fullName = $this->buildFullName($validated);

            // Create a pending login account.
            // The User model's hashed cast hashes this random password.
            $user = User::create([
                'name' => $fullName,
                'email' => $validated['email'],
                'password' => Str::random(64),
                'role' => 'student',
                'account_status' => 'pending',
            ]);

            // Create the Student record and link its login account.
            Student::create([
                'user_id' => $user->id,
                'student_id' => $validated['student_id'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'grade_level' => $validated['grade_level'],
                'sex' => $validated['sex'],
                'school_year' => $validated['school_year'],
                'email' => $validated['email'],
                'status' => $validated['status'],
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Student and pending login account created successfully.',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $student = Student::with('user')->findOrFail($id);

        $validated = $request->validate([
            'student_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('students', 'student_id')->ignore($student->id),
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'grade_level' => ['required', 'string', 'max:255'],
            'sex' => ['required', 'string', 'max:255'],
            'school_year' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('students', 'email')->ignore($student->id),
                Rule::unique('users', 'email')->ignore($student->user_id),
            ],
            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
        ]);

        DB::transaction(function () use ($student, $validated) {
            // Lock the Student record while updating it.
            $student = Student::whereKey($student->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Lock the linked User account, if one exists.
            $user = $student->user()
                ->lockForUpdate()
                ->first();

            $fullName = $this->buildFullName($validated);

            if ($user && $user->role !== 'student') {
                throw ValidationException::withMessages([
                    'email' => 'The linked account is not a student account. Please contact the administrator.',
                ]);
            }

            if (!$user) {
                // Create a pending account if the Student has no linked account.
                $user = User::create([
                    'name' => $fullName,
                    'email' => $validated['email'],
                    'password' => Str::random(64),
                    'role' => 'student',
                    'account_status' => 'pending',
                ]);
            } else {
                // Update account details without changing its password,
                // role, or account activation status.
                $user->update([
                    'name' => $fullName,
                    'email' => $validated['email'],
                ]);
            }

            $student->update([
                'user_id' => $user->id,
                'student_id' => $validated['student_id'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'grade_level' => $validated['grade_level'],
                'sex' => $validated['sex'],
                'school_year' => $validated['school_year'],
                'email' => $validated['email'],
                'status' => $validated['status'],
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Student and login account updated successfully.',
        ]);
    }

    public function destroy(
        PersonRecordService $records,
        SchoolCsvSnapshotService $snapshots,
        $id
    ) {
        // Soft-delete the Student record and linked login account.
        $records->delete('students', [$id]);

        // Refresh the Students CSV snapshot.
        $snapshots->refresh('students');

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully.',
        ]);
    }

    public function toggleStatus($id)
    {
        $student = DB::transaction(function () use ($id) {
            $student = Student::whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            $student->status = $student->status === 'active'
                ? 'inactive'
                : 'active';

            $student->save();

            return $student;
        });

        return response()->json([
            'success' => true,
            'status' => $student->status,
            'message' => $student->status === 'active'
                ? 'Student status activated successfully.'
                : 'Student status deactivated successfully.',
        ]);
    }

    private function buildFullName(array $data): string
    {
        return trim(implode(' ', array_filter([
            $data['first_name'],
            $data['middle_name'] ?? null,
            $data['last_name'],
        ])));
    }
}
