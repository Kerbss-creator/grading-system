<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Faculty;
use App\Models\TeachingAssignment;
use App\Models\AdvisoryAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserManagementController extends Controller
{
    // =====================================================
    // USER MANAGEMENT PAGE
    // =====================================================

    public function index()
    {
        return view('admin.user-management');
    }

    // =====================================================
    // GET USERS
    // =====================================================

    public function getUsers()
    {
        $users = User::with([
            'student',
            'faculty.teachingAssignments.gradeRecords',
            'faculty.advisoryAssignments',
        ])
            ->where(function ($query) {
                $query->whereHas('student')
                    ->orWhereHas('faculty');
            })
            ->get()
            ->map(function ($user) {

                $student = $user->student;
                $faculty = $user->faculty;

                $profile = $student ?? $faculty;

                $advisoryAssignments = $faculty
                    ? $faculty->advisoryAssignments
                        ->sortByDesc('id')
                        ->values()
                    : collect();

                // Latest advisory assignment for the Edit form.
                $latestAdvisory = $advisoryAssignments->first();

                return [
                    'id' => $user->id,

                    'profile_type' => $student ? 'student' : 'faculty',
                    'profile_id' => $profile?->id,

                    'profile_identifier' => $student
                        ? $student->student_id
                        : $faculty?->faculty_id,

                    'name' => $user->name,
                    'username' => $user->username ?? null,
                    'email' => $user->email,
                    'role' => $user->role,

                    // Keep account and profile status separate.
                    'status' => $user->account_status,
                    'profile_status' => $profile?->status,

                    'first_name' => $student?->first_name
                        ?? $faculty?->first_name,

                    'middle_name' => $student?->middle_name
                        ?? $faculty?->middle_name,

                    'last_name' => $student?->last_name
                        ?? $faculty?->last_name,

                    // Student details
                    'student_id' => $student?->student_id,
                    'sex' => $student?->sex,
                    'grade_level' => $student?->grade_level,
                    'section' => $student?->section,
                    'school_year' => $student?->school_year,

                    // Faculty details
                    'faculty_id' => $faculty?->faculty_id,

                    'is_teacher' => $faculty
                        ? in_array($faculty->role, [
                            'teacher',
                            'teacher_adviser',
                        ])
                        : false,

                    'is_adviser' => $faculty
                        ? in_array($faculty->role, [
                            'adviser',
                            'teacher_adviser',
                        ])
                        : false,

                    'assignments' => $faculty
                        ? $faculty->teachingAssignments->map(function ($assignment) {
                            return [
                                'id' => $assignment->id,
                                'grade_level' => $assignment->grade_level,
                                'subject' => $assignment->subject,
                                'has_grade_records' => $assignment->gradeRecords->isNotEmpty(),
                            ];
                        })->values()
                        : [],

                    // Latest advisory details for the current form.
                    'advisory_grade' => $latestAdvisory?->grade_level,
                    'advisory_school_year' => $latestAdvisory?->school_year,

                    // Preserve the complete advisory history in the response.
                    'advisory_assignments' => $advisoryAssignments
                        ->map(function ($assignment) {
                            return [
                                'id' => $assignment->id,
                                'grade_level' => $assignment->grade_level,
                                'school_year' => $assignment->school_year,
                            ];
                        })
                        ->values(),
                ];
            });

        return response()->json([
            'users' => $users,
        ]);
    }

    // =====================================================
    // CREATE USER + PROFILE
    // =====================================================

    public function store(Request $request)
    {
        $validated = $request->validate([
            'profile_type' => [
                'required',
                Rule::in(['student', 'faculty']),
            ],

            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],

            'email' => [
                'nullable',
                'email',
                'max:255',
                'unique:users,email',
            ],

            // Student information
            'student_id' => [
                'required_if:profile_type,student',
                'nullable',
                'string',
                'max:255',
                'unique:students,student_id',
            ],

            'sex' => [
                'required_if:profile_type,student',
                'nullable',
                Rule::in(['Male', 'Female']),
            ],

            'grade_level' => [
                'required_if:profile_type,student',
                'nullable',
                Rule::in(['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10']),
            ],

            'section' => ['nullable', 'string', 'max:255'],

            'school_year' => [
                'required_if:profile_type,student',
                'nullable',
                'string',
                'max:255',
            ],

            // Faculty information
            'faculty_id' => [
                'required_if:profile_type,faculty',
                'nullable',
                'string',
                'max:255',
                'unique:faculty,faculty_id',
            ],

            'is_teacher' => ['required_if:profile_type,faculty', 'boolean'],
            'is_adviser' => ['required_if:profile_type,faculty', 'boolean'],

            'assignments' => [
                'nullable',
                'array',
            ],

            'assignments.*.grade_level' => [
                'required',
                Rule::in(['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10']),
            ],

            'assignments.*.subject' => [
                'required',
                'string',
                'max:255',
            ],

            'advisory_grade' => [
                'nullable',
                Rule::in(['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10']),
            ],

            'advisory_school_year' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        // -------------------------------------------------
        // ADDITIONAL FACULTY VALIDATION
        // -------------------------------------------------

        if ($validated['profile_type'] === 'faculty') {

            $isTeacher = $request->boolean('is_teacher');
            $isAdviser = $request->boolean('is_adviser');

            if (!$isTeacher && !$isAdviser) {
                throw ValidationException::withMessages([
                    'is_teacher' => 'Select at least one faculty responsibility.',
                ]);
            }

            if ($isTeacher && empty($validated['assignments'])) {
                throw ValidationException::withMessages([
                    'assignments' => 'Add at least one teaching assignment.',
                ]);
            }

            if ($isAdviser) {
                if (
                    empty($validated['advisory_grade']) ||
                    empty($validated['advisory_school_year'])
                ) {
                    throw ValidationException::withMessages([
                        'advisory_grade' => 'Complete the advisory assignment.',
                    ]);
                }
            }
        }

        // -------------------------------------------------
        // CREATE USER AND PROFILE IN ONE TRANSACTION
        // -------------------------------------------------

        $user = DB::transaction(function () use ($validated, $request) {

            $fullName = trim(implode(' ', array_filter([
                $validated['first_name'],
                $validated['middle_name'] ?? null,
                $validated['last_name'],
            ])));

            // Student email is optional in the existing
            // Student Management workflow.
            $accountEmail = $validated['email'] ?? null;

            if (empty($accountEmail)) {

                if ($validated['profile_type'] !== 'student') {
                    throw ValidationException::withMessages([
                        'email' => 'Email address is required for faculty accounts.',
                    ]);
                }

                $studentSlug = Str::slug($validated['student_id']);

                $accountEmail = 'student-' . $studentSlug . '@students.forbes.edu';

                if (User::where('email', $accountEmail)->exists()) {
                    throw ValidationException::withMessages([
                        'email' => 'The generated student email already exists. Please provide a different email address.',
                    ]);
                }
            }

            // Create account with a random password.
            // The future setup-link feature will let the
            // account owner choose their own password.
            $user = User::create([
                'name' => $fullName,
                'email' => $accountEmail,
                'password' => Hash::make(Str::random(64)),
                'role' => 'student',
                'account_status' => 'pending',
            ]);

            // =============================================
            // STUDENT PROFILE
            // =============================================

            if ($validated['profile_type'] === 'student') {

                Student::create([
                    'user_id' => $user->id,

                    'student_id' => $validated['student_id'],

                    'first_name' => $validated['first_name'],

                    'middle_name' => $validated['middle_name'] ?? null,

                    'last_name' => $validated['last_name'],

                    'sex' => $validated['sex'],

                    'grade_level' => $validated['grade_level'],

                    'section' => $validated['section'] ?? null,

                    'school_year' => $validated['school_year'],

                    'email' => $validated['email'] ?? null,

                    'status' => 'active',
                ]);

                $user->update([
                    'role' => 'student',
                ]);
            }

            // =============================================
            // FACULTY PROFILE
            // =============================================

            if ($validated['profile_type'] === 'faculty') {

                $isTeacher = $request->boolean('is_teacher');
                $isAdviser = $request->boolean('is_adviser');

                if ($isTeacher && $isAdviser) {
                    $role = 'teacher_adviser';
                } elseif ($isAdviser) {
                    $role = 'adviser';
                } else {
                    $role = 'teacher';
                }

                $user->update([
                    'role' => $role,
                ]);

                $faculty = Faculty::create([
                    'user_id' => $user->id,

                    'faculty_id' => $validated['faculty_id'],

                    'first_name' => $validated['first_name'],

                    'middle_name' => $validated['middle_name'] ?? null,

                    'last_name' => $validated['last_name'],

                    'email' => $validated['email'],

                    'role' => $role,

                    'status' => 'active',
                ]);

                // -----------------------------------------
                // TEACHING ASSIGNMENTS
                // -----------------------------------------

                if ($isTeacher) {

                    foreach ($validated['assignments'] as $assignment) {

                        $faculty->teachingAssignments()->create([
                            'grade_level' => $assignment['grade_level'],
                            'subject' => $assignment['subject'],
                        ]);
                    }
                }

                // -----------------------------------------
                // ADVISORY ASSIGNMENT
                // -----------------------------------------

                if ($isAdviser) {

                    $gradeLevel = $validated['advisory_grade'];
                    $schoolYear = $validated['advisory_school_year'];

                    // One adviser per grade level per school year.
                    $existingAdviser = AdvisoryAssignment::where(
                        'grade_level',
                        $gradeLevel
                    )
                        ->where('school_year', $schoolYear)
                        ->lockForUpdate()
                        ->exists();

                    if ($existingAdviser) {
                        throw ValidationException::withMessages([
                            'advisory_grade' =>
                                "An adviser is already assigned to {$gradeLevel} for school year {$schoolYear}.",
                        ]);
                    }

                    $faculty->advisoryAssignments()->create([
                        'grade_level' => $gradeLevel,
                        'school_year' => $schoolYear,
                    ]);
                }
            }

            return $user;
        });

        return response()->json([
            'message' => 'Account and profile created successfully. Account status is Pending.',
            'user' => $user->load(['student', 'faculty']),
        ], 201);
    }

    // =====================================================
    // UPDATE USER EMAIL
    // =====================================================

    public function update(Request $request, User $user)
    {
        $student = $user->student;
        $faculty = $user->faculty;

        // A user must have an existing Student or Faculty profile.
        if (!$student && !$faculty) {
            return response()->json([
                'message' => 'No Student or Faculty profile is attached to this account.',
            ], 422);
        }

        // Do not allow changing Student into Faculty or vice versa.
        $profileType = $student ? 'student' : 'faculty';

        $validated = $request->validate([
            'profile_type' => [
                'required',
                Rule::in([$profileType]),
            ],

            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],

            'email' => [
                $profileType === 'faculty' ? 'required' : 'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
                Rule::unique('faculty', 'email')->ignore($faculty?->id),
            ],

            // Student details
            'student_id' => [
                'required_if:profile_type,student',
                'nullable',
                'string',
                'max:255',
                Rule::unique('students', 'student_id')->ignore($student?->id),
            ],

            'sex' => [
                'required_if:profile_type,student',
                'nullable',
                Rule::in(['Male', 'Female']),
            ],

            'grade_level' => [
                'required_if:profile_type,student',
                'nullable',
                Rule::in(['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10']),
            ],

            'section' => ['nullable', 'string', 'max:255'],

            'school_year' => [
                'required_if:profile_type,student',
                'nullable',
                'string',
                'max:255',
            ],

            // Faculty details
            'faculty_id' => [
                'required_if:profile_type,faculty',
                'nullable',
                'string',
                'max:255',
                Rule::unique('faculty', 'faculty_id')->ignore($faculty?->id),
            ],

            'is_teacher' => ['required_if:profile_type,faculty', 'boolean'],
            'is_adviser' => ['required_if:profile_type,faculty', 'boolean'],

            'assignments' => ['nullable', 'array'],

            'assignments.*.grade_level' => [
                'required',
                Rule::in(['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10']),
            ],

            'assignments.*.subject' => [
                'required',
                'string',
                'max:255',
            ],

            'advisory_grade' => [
                'nullable',
                Rule::in(['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10']),
            ],

            'advisory_school_year' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        // Additional Faculty validation.
        if ($profileType === 'faculty') {

            $isTeacher = $request->boolean('is_teacher');
            $isAdviser = $request->boolean('is_adviser');

            if (!$isTeacher && !$isAdviser) {
                throw ValidationException::withMessages([
                    'is_teacher' => 'Select at least one faculty responsibility.',
                ]);
            }

            if ($isTeacher && empty($validated['assignments'])) {
                throw ValidationException::withMessages([
                    'assignments' => 'Add at least one teaching assignment.',
                ]);
            }

            if (
                $isAdviser &&
                (
                    empty($validated['advisory_grade']) ||
                    empty($validated['advisory_school_year'])
                )
            ) {
                throw ValidationException::withMessages([
                    'advisory_grade' => 'Complete the advisory assignment.',
                ]);
            }
        }

        DB::transaction(function () use ($validated, $user, $student, $faculty, $profileType, $request) {

            $fullName = trim(implode(' ', array_filter([
                $validated['first_name'],
                $validated['middle_name'] ?? null,
                $validated['last_name'],
            ])));

            // For Students, keep the existing generated login email
            // when the optional profile email is left blank.
            $accountEmail = $validated['email'] ?? $user->email;

            $user->update([
                'name' => $fullName,
                'email' => $accountEmail,
            ]);

            // =============================================
            // UPDATE STUDENT PROFILE
            // =============================================

            if ($profileType === 'student') {

                $student->update([
                    'student_id' => $validated['student_id'],
                    'first_name' => $validated['first_name'],
                    'middle_name' => $validated['middle_name'] ?? null,
                    'last_name' => $validated['last_name'],
                    'sex' => $validated['sex'],
                    'grade_level' => $validated['grade_level'],
                    'section' => $validated['section'] ?? null,
                    'school_year' => $validated['school_year'],
                    'email' => $validated['email'] ?? null,
                ]);

                $user->update([
                    'role' => 'student',
                ]);
            }

            // =============================================
            // UPDATE FACULTY PROFILE
            // =============================================

            if ($profileType === 'faculty') {

                $isTeacher = $request->boolean('is_teacher');
                $isAdviser = $request->boolean('is_adviser');

                if ($isTeacher && $isAdviser) {
                    $role = 'teacher_adviser';
                } elseif ($isAdviser) {
                    $role = 'adviser';
                } else {
                    $role = 'teacher';
                }

                $faculty->update([
                    'faculty_id' => $validated['faculty_id'],
                    'first_name' => $validated['first_name'],
                    'middle_name' => $validated['middle_name'] ?? null,
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'role' => $role,
                ]);

                $user->update([
                    'role' => $role,
                ]);

                // -----------------------------------------
                // TEACHING ASSIGNMENTS
                // -----------------------------------------

                $submittedAssignments = $isTeacher
                    ? ($validated['assignments'] ?? [])
                    : [];

                $existingAssignments = $faculty->teachingAssignments()
                    ->with('gradeRecords')
                    ->get();

                foreach ($existingAssignments as $existing) {

                    $stillSubmitted = collect($submittedAssignments)->contains(
                        function ($assignment) use ($existing) {
                            return $assignment['grade_level'] === $existing->grade_level
                                && $assignment['subject'] === $existing->subject;
                        }
                    );

                    if ($stillSubmitted) {
                        continue;
                    }

                    // Preserve assignments referenced by grade records.
                    if ($existing->gradeRecords()->exists()) {
                        continue;
                    }

                    // Safe to remove only when no grade history exists.
                    $existing->delete();
                }

                // Add only assignments that do not already exist.
                foreach ($submittedAssignments as $assignment) {

                    $alreadyExists = $faculty->teachingAssignments()
                        ->where('grade_level', $assignment['grade_level'])
                        ->where('subject', $assignment['subject'])
                        ->exists();

                    if (!$alreadyExists) {
                        $faculty->teachingAssignments()->create([
                            'grade_level' => $assignment['grade_level'],
                            'subject' => $assignment['subject'],
                        ]);
                    }
                }

                // -----------------------------------------
                // ADVISORY ASSIGNMENT - PRESERVE HISTORY
                // -----------------------------------------

                if ($isAdviser) {

                    $gradeLevel = $validated['advisory_grade'];
                    $schoolYear = $validated['advisory_school_year'];

                    // Check whether this exact assignment already
                    // exists for this faculty member.
                    $sameAssignmentExists = $faculty->advisoryAssignments()
                        ->where('grade_level', $gradeLevel)
                        ->where('school_year', $schoolYear)
                        ->exists();

                    if (!$sameAssignmentExists) {

                        // Another faculty member cannot own the
                        // same grade level for the same school year.
                        $existingAdviser = AdvisoryAssignment::where(
                            'grade_level',
                            $gradeLevel
                        )
                            ->where('school_year', $schoolYear)
                            ->where('faculty_id', '!=', $faculty->id)
                            ->lockForUpdate()
                            ->exists();

                        if ($existingAdviser) {
                            throw ValidationException::withMessages([
                                'advisory_grade' =>
                                    "An adviser is already assigned to {$gradeLevel} for school year {$schoolYear}.",
                            ]);
                        }

                        // Create a new record instead of modifying
                        // or deleting the previous advisory history.
                        $faculty->advisoryAssignments()->create([
                            'grade_level' => $gradeLevel,
                            'school_year' => $schoolYear,
                        ]);
                    }
                }

                // When Adviser responsibility is unchecked,
                // existing advisory records are intentionally preserved.
            }
        });

        return response()->json([
            'message' => 'Account and profile updated successfully. Existing academic records were preserved.',
        ]);
    }

    // =====================================================
    // ACTIVATE / DISABLE ACCOUNT
    // =====================================================

    public function toggleStatus(Request $request, User $user)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
        ]);

        $user->update([
            'account_status' => $validated['status'],
        ]);

        return response()->json([
            'message' => 'Account status updated successfully.',
            'status' => $user->account_status,
        ]);
    }

    // =====================================================
    // RESET PASSWORD
    // =====================================================

    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Password reset successfully.',
        ]);
    }
}
