<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Services\PersonRecordService;
use App\Services\SchoolCsvSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
        // Generate an account email if the student has no email.
        $accountEmail = $request->input('email')
            ?: $request->input('student_id') . '@forbes.edu';

        $request->merge([
            'account_email' => $accountEmail,
        ]);

        $validated = $request->validate([
            'student_id' => 'required|string|unique:students,student_id',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'grade_level' => 'required|string|max:255',
            'sex' => 'required|string|max:255',
            'school_year' => 'required|string|max:255',
            'email' => 'nullable|email',
            'account_email' => 'required|email|unique:users,email',
            'status' => 'required|in:active,inactive',
        ]);

        DB::transaction(function () use ($validated) {

            // Create login account.
            $user = User::create([
                'name' => trim(
                    $validated['first_name'] . ' ' .
                    $validated['last_name']
                ),
                'email' => $validated['account_email'],
                'password' => 'password123',
                'role' => 'student',
            ]);

            // Create student record and connect its login account.
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
            'message' => 'Student and login account created successfully.'
        ]);
    }

    public function update(Request $request, $id)
    {
        $student = Student::with('user')->findOrFail($id);

        $accountEmail = $request->input('email')
            ?: $request->input('student_id') . '@forbes.edu';

        $request->merge([
            'account_email' => $accountEmail,
        ]);

        $userId = $student->user_id;

        $validated = $request->validate([
            'student_id' => [
                'required',
                'string',
                Rule::unique('students', 'student_id')->ignore($student->id),
            ],
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'grade_level' => 'required|string|max:255',
            'sex' => 'required|string|max:255',
            'school_year' => 'required|string|max:255',
            'email' => 'nullable|email',
            'account_email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'status' => 'required|in:active,inactive',
        ]);

        DB::transaction(function () use ($student, $validated) {

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
                'email' => $validated['email'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        return response()->json([
            'success' => true,
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
        ]);
    }

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
            'message' => 'Student status updated.'
        ]);
    }
}
