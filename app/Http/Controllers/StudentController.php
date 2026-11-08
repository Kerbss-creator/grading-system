<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Services\PersonRecordService;
use App\Services\SchoolCsvSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
<<<<<<< HEAD
=======
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
>>>>>>> grading-system2

class StudentController extends Controller
{
    /**
     * Display all student records.
     */
    public function index()
    {
        $students = Student::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.student-management', compact('students'));
    }

    /**
     * Create a Student record and its linked User account.
     */
    public function store(Request $request)
    {
        // Generate an account email if the student has no email.
        $accountEmail = $request->input('email')
            ?: $request->input('student_id') . '@forbes.edu';

        $request->merge([
            'account_email' => $accountEmail,
        ]);

        $validated = $request->validate([
<<<<<<< HEAD
            'student_id' => 'required|string|unique:students,student_id',
=======
            'student_id' => 'required|string|max:255|unique:students,student_id',
>>>>>>> grading-system2
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'grade_level' => 'required|string|max:255',
            'sex' => 'required|string|max:255',
            'school_year' => 'required|string|max:255',
<<<<<<< HEAD
            'email' => 'nullable|email',
            'account_email' => 'required|email|unique:users,email',
=======
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
>>>>>>> grading-system2
            'status' => 'required|in:active,inactive',
        ]);

        DB::transaction(function () use ($validated) {

<<<<<<< HEAD
            // Create login account.
            $user = User::create([
                'name' => trim(
                    $validated['first_name'] . ' ' .
                    $validated['last_name']
                ),
                'email' => $validated['account_email'],
=======
            $accountEmail = $validated['email']
                ?: 'student-' . Str::slug($validated['student_id']) . '@students.forbes.edu';

            // Prevent collisions with existing login accounts.
            if (User::where('email', $accountEmail)->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'The generated student login email already exists. Please provide another email address.',
                ]);
            }

            $fullName = $this->buildFullName($validated);

            // Create login account.
            // The User model's hashed cast automatically hashes this password.
            $user = User::create([
                'name' => $fullName,
                'email' => $accountEmail,
>>>>>>> grading-system2
                'password' => 'password123',
                'role' => 'student',
            ]);

<<<<<<< HEAD
            // Create student record and connect its login account.
=======
            // Create Student record and link it to the User.
>>>>>>> grading-system2
            Student::create([
                'user_id' => $user->id,
                'student_id' => $validated['student_id'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'grade_level' => $validated['grade_level'],
                'sex' => $validated['sex'],
                'school_year' => $validated['school_year'],
                'email' => $validated['email'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        return response()->json([
            'success' => true,
<<<<<<< HEAD
            'message' => 'Student and login account created successfully.'
=======
            'message' => 'Student added successfully.',
>>>>>>> grading-system2
        ]);
    }

    /**
     * Update Student information and its linked User account.
     */
    public function update(Request $request, $id)
    {
<<<<<<< HEAD
        $student = Student::with('user')->findOrFail($id);

        $accountEmail = $request->input('email')
            ?: $request->input('student_id') . '@forbes.edu';

        $request->merge([
            'account_email' => $accountEmail,
        ]);

        $userId = $student->user_id;
=======
        $student = Student::findOrFail($id);
>>>>>>> grading-system2

        $validated = $request->validate([
            'student_id' => [
                'required',
                'string',
<<<<<<< HEAD
=======
                'max:255',
>>>>>>> grading-system2
                Rule::unique('students', 'student_id')->ignore($student->id),
            ],
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'grade_level' => 'required|string|max:255',
            'sex' => 'required|string|max:255',
            'school_year' => 'required|string|max:255',
<<<<<<< HEAD
            'email' => 'nullable|email',
            'account_email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
=======
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($student->user_id),
>>>>>>> grading-system2
            ],
            'status' => 'required|in:active,inactive',
        ]);

        DB::transaction(function () use ($student, $validated) {

<<<<<<< HEAD
            // Update existing account or create one if missing.
            $user = $student->user;

            if (!$user) {
                $user = User::create([
                    'name' => trim(
                        $validated['first_name'] . ' ' .
                        $validated['last_name']
                    ),
                    'email' => $validated['account_email'],
                    'password' => 'password123',
                    'role' => 'student',
                ]);
            } else {
                $user->update([
                    'name' => trim(
                        $validated['first_name'] . ' ' .
                        $validated['last_name']
                    ),
                    'email' => $validated['account_email'],
=======
            $student = Student::whereKey($student->id)
                ->lockForUpdate()
                ->firstOrFail();

            $user = $student->user()
                ->lockForUpdate()
                ->first();

            $accountEmail = $validated['email']
                ?: ($user?->email ?? 'student-' . Str::slug($validated['student_id']) . '@students.forbes.edu');

            // Ensure the new or generated email is not used by another account.
            $emailExists = User::where('email', $accountEmail)
                ->when($user, function ($query) use ($user) {
                    $query->where('id', '!=', $user->id);
                })
                ->exists();

            if ($emailExists) {
                throw ValidationException::withMessages([
                    'email' => 'This login email is already being used by another account.',
                ]);
            }

            $fullName = $this->buildFullName($validated);

            if ($user && $user->role !== 'student') {
                throw ValidationException::withMessages([
                    'email' => 'The linked account is not a student account. Please contact the administrator.',
                ]);
            }

            if (!$user) {
                // Repair a missing linked account without duplicating existing accounts.
                $user = User::create([
                    'name' => $fullName,
                    'email' => $accountEmail,
                    'password' => 'password123',
                    'role' => 'student',
                ]);

                $student->user_id = $user->id;
            } else {
                // Update account details but preserve the existing password.
                $user->update([
                    'name' => $fullName,
                    'email' => $accountEmail,
>>>>>>> grading-system2
                ]);
            }

            $student->update([
<<<<<<< HEAD
                'user_id' => $user->id,
=======
>>>>>>> grading-system2
                'student_id' => $validated['student_id'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'grade_level' => $validated['grade_level'],
<<<<<<< HEAD
=======
                'section' => $validated['section'] ?? null,
>>>>>>> grading-system2
                'sex' => $validated['sex'],
                'school_year' => $validated['school_year'],
                'email' => $validated['email'] ?? null,
                'status' => $validated['status'],
<<<<<<< HEAD
=======
                'user_id' => $user->id,
>>>>>>> grading-system2
            ]);
        });

        return response()->json([
            'success' => true,
<<<<<<< HEAD
            'message' => 'Student and login account updated successfully.'
        ]);
    }

    public function destroy(
        PersonRecordService $records,
        SchoolCsvSnapshotService $snapshots,
        $id
    ) {
        // Soft-delete student record and linked login account.
        $records->delete('students', [$id]);

        // Refresh the students CSV snapshot.
        $snapshots->refresh('students');

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully.'
=======
            'message' => 'Student updated successfully.',
        ]);
    }

    /**
     * Deactivate a Student instead of permanently deleting the record.
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        $student->update([
            'status' => 'inactive',
        ]);

        return response()->json([
            'success' => true,
            'status' => 'inactive',
            'message' => 'Student account deactivated successfully.',
>>>>>>> grading-system2
        ]);
    }

    /**
     * Activate or deactivate a Student account.
     */
    public function toggleStatus($id)
    {
        $student = Student::findOrFail($id);

        $student->status = $student->status === 'active'
            ? 'inactive'
            : 'active';

        $student->save();

        return response()->json([
            'success' => true,
            'status' => $student->status,
            'message' => $student->status === 'active'
                ? 'Student account activated successfully.'
                : 'Student account deactivated successfully.',
        ]);
    }
<<<<<<< HEAD
}
=======

    /**
     * Build the complete Student name.
     */
    private function buildFullName(array $data): string
    {
        return trim(implode(' ', array_filter([
            $data['first_name'],
            $data['middle_name'] ?? null,
            $data['last_name'],
        ])));
    }
}
>>>>>>> grading-system2
