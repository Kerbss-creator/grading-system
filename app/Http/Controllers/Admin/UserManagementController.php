<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Faculty;
use App\Models\TeachingAssignment;
use App\Models\AdvisoryAssignment;
use App\Models\SchoolYear;
use App\Services\ActivityRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

                    'status' => $user->account_status,
                    'profile_status' => $profile?->status,

                    'first_name' => $student?->first_name
                        ?? $faculty?->first_name,

                    'middle_name' => $student?->middle_name
                        ?? $faculty?->middle_name,

                    'last_name' => $student?->last_name
                        ?? $faculty?->last_name,

                    // STUDENT DETAILS
                    'student_id' => $student?->student_id,
                    'sex' => $student?->sex,
                    'grade_level' => $student?->grade_level,
                    'section' => $student?->section,
                    'school_year' => $student?->school_year,

                    // FACULTY DETAILS
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

                    // TEACHING ASSIGNMENTS
                    'assignments' => $faculty
                        ? $faculty->teachingAssignments
                            ->sortBy('id')
                            ->map(function ($assignment) {
                                return [
                                    'id' => $assignment->id,
                                    'grade_level' => $assignment->grade_level,
                                    'subject' => $assignment->subject,
                                    'component' => $assignment->component ?? 'General',
                                    'school_year' => $assignment->school_year,
                                    'has_grade_records' => $assignment->gradeRecords->isNotEmpty(),
                                ];
                            })
                            ->values()
                        : [],

                    // LATEST ADVISORY ASSIGNMENT
                    'advisory_grade' => $latestAdvisory?->grade_level,
                    'advisory_school_year' => $latestAdvisory?->school_year,

                    // COMPLETE ADVISORY HISTORY
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

    public function store(Request $request, ActivityRecorder $audit)
    {
        $validated = $request->validate([

            'profile_type' => [
                'required',
                Rule::in(['student', 'faculty']),
            ],

            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],

            // A REAL EMAIL ADDRESS IS REQUIRED.
            // No automatically generated student emails.
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
                'unique:faculty,email',
            ],

            // STUDENT INFORMATION

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

            // FACULTY INFORMATION

            'faculty_id' => [
                'required_if:profile_type,faculty',
                'nullable',
                'string',
                'max:255',
                'unique:faculty,faculty_id',
            ],

            'is_teacher' => [
                'required_if:profile_type,faculty',
                'boolean',
            ],

            'is_adviser' => [
                'required_if:profile_type,faculty',
                'boolean',
            ],

            // TEACHING ASSIGNMENTS

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

            'assignments.*.component' => [
                'nullable',
                Rule::in(['General', 'Music and Arts', 'PE and Health']),
            ],

            // ADVISORY ASSIGNMENT

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

        // -------------------------------------------------
        // PREPARE TEACHING ASSIGNMENTS
        // -------------------------------------------------

        $activeSchoolYear = null;
        $normalizedAssignments = [];

        if (
            $validated['profile_type'] === 'faculty' &&
            $request->boolean('is_teacher')
        ) {
            $activeSchoolYear = $this->getActiveSchoolYear();

            $normalizedAssignments = $this->normalizeTeachingAssignments(
                $validated['assignments'] ?? []
            );

            $this->validateTeachingAssignmentAvailability(
                $normalizedAssignments,
                $activeSchoolYear
            );
        }

        // -------------------------------------------------
        // CREATE USER AND PROFILE
        // -------------------------------------------------

        $user = DB::transaction(function () use ($validated, $request, $activeSchoolYear, $normalizedAssignments, $audit) {

            $fullName = trim(implode(' ', array_filter([
                $validated['first_name'],
                $validated['middle_name'] ?? null,
                $validated['last_name'],
            ])));

            // Use the actual email provided.
            $accountEmail = trim($validated['email']);

            // CREATE USER ACCOUNT

            $user = User::create([
                'name' => $fullName,
                'email' => $accountEmail,
                'password' => Hash::make(\Illuminate\Support\Str::random(64)),
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
                    'email' => $accountEmail,
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
                    'email' => $accountEmail,
                    'role' => $role,
                    'status' => 'active',
                ]);

                // -----------------------------------------
                // TEACHING ASSIGNMENTS
                // -----------------------------------------

                if ($isTeacher) {

                    foreach ($normalizedAssignments as $assignment) {

                        $faculty->teachingAssignments()->create([
                            'grade_level' => $assignment['grade_level'],
                            'subject' => $assignment['subject'],
                            'component' => $assignment['component'],
                            'school_year' => $activeSchoolYear,
                        ]);
                    }
                }

                // -----------------------------------------
                // ADVISORY ASSIGNMENT
                // -----------------------------------------

                if ($isAdviser) {

                    $gradeLevel = $validated['advisory_grade'];
                    $schoolYear = $validated['advisory_school_year'];

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

            // -----------------------------------------
            // AUDIT LOG: CREATE
            // -----------------------------------------

            $user->load([
                'student',
                'faculty.teachingAssignments',
                'faculty.advisoryAssignments',
            ]);

            $audit->record(
                'create',
                'users',
                (string) $user->id,
                null,
                $this->userAuditSnapshot($user),
                [
                    'source' => 'user_management',
                    'profile_type' => $validated['profile_type'],
                ]
            );

            return $user;
        });

        return response()->json([
            'message' => 'Account and profile created successfully. Account status is Pending.',
            'user' => $user->load(['student', 'faculty']),
        ], 201);
    }

    // =====================================================
    // UPDATE USER
    // =====================================================

    public function update(
        Request $request,
        User $user,
        ActivityRecorder $audit
    ) {
        $student = $user->student;
        $faculty = $user->faculty;

        if (!$student && !$faculty) {
            return response()->json([
                'message' => 'No Student or Faculty profile is attached to this account.',
            ], 422);
        }

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
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
                Rule::unique('faculty', 'email')->ignore($faculty?->id),
            ],

            // STUDENT DETAILS

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

            // FACULTY DETAILS

            'faculty_id' => [
                'required_if:profile_type,faculty',
                'nullable',
                'string',
                'max:255',
                Rule::unique('faculty', 'faculty_id')->ignore($faculty?->id),
            ],

            'is_teacher' => [
                'required_if:profile_type,faculty',
                'boolean',
            ],

            'is_adviser' => [
                'required_if:profile_type,faculty',
                'boolean',
            ],

            // TEACHING ASSIGNMENTS

            'assignments' => ['nullable', 'array'],

            'assignments.*.id' => [
                'nullable',
                'integer',
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

            'assignments.*.component' => [
                'nullable',
                Rule::in(['General', 'Music and Arts', 'PE and Health']),
            ],

            // ADVISORY DETAILS

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

        // -------------------------------------------------
        // PREPARE TEACHING ASSIGNMENTS
        // -------------------------------------------------

        $activeSchoolYear = SchoolYear::active()?->year_name;
        $normalizedAssignments = [];

        if ($profileType === 'faculty') {

            if (
                $request->boolean('is_teacher') &&
                !$activeSchoolYear
            ) {
                throw ValidationException::withMessages([
                    'assignments' => 'There is no active school year. Please set an active school year first.',
                ]);
            }

            if ($request->boolean('is_teacher')) {

                $normalizedAssignments = $this->normalizeTeachingAssignments(
                    $validated['assignments'] ?? []
                );

                $this->validateTeachingAssignmentAvailability(
                    $normalizedAssignments,
                    $activeSchoolYear,
                    $faculty->id
                );
            }
        }

        // -------------------------------------------------
        // UPDATE ACCOUNT AND PROFILE
        // -------------------------------------------------

        $updatedUser = DB::transaction(function () use ($validated, $user, $profileType, $request, $activeSchoolYear, $normalizedAssignments, $audit) {

            // Reload and lock the account before updating.
            $user = User::whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $user->load([
                'student',
                'faculty.teachingAssignments',
                'faculty.advisoryAssignments',
            ]);

            $student = $user->student;
            $faculty = $user->faculty;

            $before = $this->userAuditSnapshot($user);

            $fullName = trim(implode(' ', array_filter([
                $validated['first_name'],
                $validated['middle_name'] ?? null,
                $validated['last_name'],
            ])));

            $accountEmail = trim($validated['email']);

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
                    'email' => $accountEmail,
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
                    'email' => $accountEmail,
                    'role' => $role,
                ]);

                $user->update([
                    'role' => $role,
                ]);

                // -----------------------------------------
                // TEACHING ASSIGNMENTS
                // -----------------------------------------

                // Only reconcile the active school year.
                // Historical assignments are not touched.
                if ($activeSchoolYear) {

                    $this->reconcileTeachingAssignments(
                        $faculty,
                        $isTeacher ? $normalizedAssignments : [],
                        $activeSchoolYear
                    );
                }

                // -----------------------------------------
                // ADVISORY ASSIGNMENT - PRESERVE HISTORY
                // -----------------------------------------

                if ($isAdviser) {

                    $gradeLevel = $validated['advisory_grade'];
                    $schoolYear = $validated['advisory_school_year'];

                    $existingAssignments = $faculty->advisoryAssignments()
                        ->where('school_year', $schoolYear)
                        ->lockForUpdate()
                        ->get();

                    if ($existingAssignments->count() > 1) {
                        throw ValidationException::withMessages([
                            'advisory_grade' =>
                                'Multiple advisory assignments already exist for this faculty member in the selected school year. Please review the records first.',
                        ]);
                    }

                    $existingAssignment = $existingAssignments->first();

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

                    if ($existingAssignment) {

                        $existingAssignment->update([
                            'grade_level' => $gradeLevel,
                        ]);

                    } else {

                        $faculty->advisoryAssignments()->create([
                            'grade_level' => $gradeLevel,
                            'school_year' => $schoolYear,
                        ]);
                    }
                }

                // Preserve historical assignments when
                // Adviser responsibility is unchecked.
            }

            // -----------------------------------------
            // AUDIT LOG: UPDATE
            // -----------------------------------------

            $user->load([
                'student',
                'faculty.teachingAssignments',
                'faculty.advisoryAssignments',
            ]);

            $after = $this->userAuditSnapshot($user);

            if ($before !== $after) {
                $audit->record(
                    'update',
                    'users',
                    (string) $user->id,
                    $before,
                    $after,
                    [
                        'source' => 'user_management',
                        'profile_type' => $profileType,
                    ]
                );
            }

            return $user;
        });

        return response()->json([
            'message' => 'Account and profile updated successfully. Existing academic records were preserved.',
            'user' => $updatedUser->load(['student', 'faculty']),
        ]);
    }

    // =====================================================
    // ACTIVATE / DISABLE ACCOUNT
    // =====================================================

    public function toggleStatus(
        Request $request,
        User $user,
        ActivityRecorder $audit
    ) {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
        ]);

        $before = [
            'account_status' => $user->account_status,
        ];

        $user->update([
            'account_status' => $validated['status'],
        ]);

        $after = [
            'account_status' => $user->fresh()->account_status,
        ];

        if ($before !== $after) {
            $audit->record(
                'status_change',
                'users',
                (string) $user->id,
                $before,
                $after,
                [
                    'source' => 'user_management',
                ]
            );
        }

        return response()->json([
            'message' => 'Account status updated successfully.',
            'status' => $user->fresh()->account_status,
        ]);
    }

    // =====================================================
    // RESET PASSWORD
    // =====================================================

    public function resetPassword(
        Request $request,
        User $user,
        ActivityRecorder $audit
    ) {
        $validated = $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Never store the actual password or its hash
        // in the activity log.
        $audit->record(
            'password_reset',
            'users',
            (string) $user->id,
            null,
            null,
            [
                'source' => 'user_management',
                'message' => 'User password was reset by an administrator.',
            ]
        );

        return response()->json([
            'message' => 'Password reset successfully.',
        ]);
    }

    // =====================================================
    // AUDIT SNAPSHOT
    // =====================================================

    /**
     * Build a safe before/after snapshot.
     *
     * Passwords and password hashes are intentionally excluded.
     */
    private function userAuditSnapshot(User $user): array
    {
        $user->loadMissing([
            'student',
            'faculty.teachingAssignments',
            'faculty.advisoryAssignments',
        ]);

        $student = $user->student;
        $faculty = $user->faculty;

        return [
            'user' => $user->only([
                'name',
                'email',
                'role',
                'account_status',
            ]),

            'profile_type' => $student
                ? 'student'
                : ($faculty ? 'faculty' : null),

            'student' => $student
                ? $student->only([
                    'student_id',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'sex',
                    'grade_level',
                    'section',
                    'school_year',
                    'email',
                    'status',
                ])
                : null,

            'faculty' => $faculty
                ? $faculty->only([
                    'faculty_id',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'email',
                    'role',
                    'status',
                ])
                : null,

            'teaching_assignments' => $faculty
                ? $faculty->teachingAssignments
                    ->sortBy('id')
                    ->map(fn($assignment) => $assignment->only([
                        'grade_level',
                        'subject',
                        'component',
                        'school_year',
                    ]))
                    ->values()
                    ->all()
                : [],

            'advisory_assignments' => $faculty
                ? $faculty->advisoryAssignments
                    ->sortBy('id')
                    ->map(fn($assignment) => $assignment->only([
                        'grade_level',
                        'school_year',
                    ]))
                    ->values()
                    ->all()
                : [],
        ];
    }

    // =====================================================
    // TEACHING ASSIGNMENT HELPERS
    // =====================================================

    /**
     * Get the active school year.
     */
    private function getActiveSchoolYear(): string
    {
        $schoolYear = SchoolYear::active()?->year_name;

        if (!$schoolYear) {
            throw ValidationException::withMessages([
                'assignments' => 'There is no active school year. Please set an active school year first.',
            ]);
        }

        return $schoolYear;
    }

    /**
     * Normalize teaching assignments.
     *
     * Non-MAPEH subjects use General.
     * MAPEH requires a valid component.
     */
    private function normalizeTeachingAssignments(array $assignments): array
    {
        $normalized = [];
        $seen = [];

        foreach ($assignments as $index => $assignment) {

            $gradeLevel = $assignment['grade_level'] ?? null;
            $subject = trim($assignment['subject'] ?? '');

            if (!$gradeLevel || $subject === '') {
                throw ValidationException::withMessages([
                    "assignments.{$index}" =>
                        'Each teaching assignment must have a grade level and subject.',
                ]);
            }

            if ($subject === 'MAPEH') {

                $component = $assignment['component'] ?? null;

                if (
                    !in_array($component, [
                        'Music and Arts',
                        'PE and Health',
                    ], true)
                ) {
                    throw ValidationException::withMessages([
                        "assignments.{$index}.component" =>
                            'Select either Music and Arts or PE and Health for MAPEH.',
                    ]);
                }

            } else {
                $component = 'General';
            }

            $key = implode('|', [
                $gradeLevel,
                $subject,
                $component,
            ]);

            if (isset($seen[$key])) {
                throw ValidationException::withMessages([
                    'assignments' =>
                        "Duplicate teaching assignment detected: {$gradeLevel} - {$subject} - {$component}.",
                ]);
            }

            $seen[$key] = true;

            $normalized[] = [
                'id' => $assignment['id'] ?? null,
                'grade_level' => $gradeLevel,
                'subject' => $subject,
                'component' => $component,
            ];
        }

        return $normalized;
    }

    /**
     * Check if another faculty member already owns
     * the same grade, subject, component, and school year.
     */
    private function validateTeachingAssignmentAvailability(
        array $assignments,
        string $schoolYear,
        ?int $facultyId = null
    ): void {

        foreach ($assignments as $assignment) {

            $query = TeachingAssignment::query()
                ->where('grade_level', $assignment['grade_level'])
                ->where('subject', $assignment['subject'])
                ->where('component', $assignment['component'])
                ->where('school_year', $schoolYear);

            if ($facultyId) {
                $query->where('faculty_id', '!=', $facultyId);
            }

            if ($query->exists()) {

                $componentLabel = $assignment['subject'] === 'MAPEH'
                    ? " ({$assignment['component']})"
                    : '';

                throw ValidationException::withMessages([
                    'assignments' =>
                        "{$assignment['subject']}{$componentLabel} is already assigned to another teacher for {$assignment['grade_level']} in school year {$schoolYear}.",
                ]);
            }
        }
    }

    /**
     * Reconcile only the active school year's assignments.
     *
     * Historical assignments are never modified or deleted.
     * Assignments with grade records are preserved.
     */
    private function reconcileTeachingAssignments(
        Faculty $faculty,
        array $submittedAssignments,
        string $schoolYear
    ): void {

        $existingAssignments = $faculty->teachingAssignments()
            ->with('gradeRecords')
            ->where('school_year', $schoolYear)
            ->lockForUpdate()
            ->get();

        $matchedAssignments = [];
        $resolvedAssignments = [];

        // ---------------------------------------------
        // MATCH SUBMITTED ASSIGNMENTS FIRST
        // ---------------------------------------------

        foreach ($submittedAssignments as $assignment) {

            $existing = null;

            // Match by assignment ID.
            if (!empty($assignment['id'])) {

                $existing = $existingAssignments->firstWhere(
                    'id',
                    (int) $assignment['id']
                );

                if (!$existing) {
                    throw ValidationException::withMessages([
                        'assignments' =>
                            'An assignment does not belong to this faculty member or the active school year. Please refresh the page.',
                    ]);
                }
            }

            // Match by exact assignment details when no ID exists.
            if (!$existing) {

                $existing = $existingAssignments->first(function ($item) use ($assignment, $matchedAssignments) {
                    return !in_array(
                        $item->id,
                        $matchedAssignments,
                        true
                    )
                        && $item->grade_level === $assignment['grade_level']
                        && $item->subject === $assignment['subject']
                        && ($item->component ?? 'General') === $assignment['component'];
                });
            }

            if ($existing) {
                $matchedAssignments[] = $existing->id;
            }

            $resolvedAssignments[] = [
                'data' => $assignment,
                'existing' => $existing,
            ];
        }

        // ---------------------------------------------
        // REMOVE OMITTED ACTIVE-YEAR ASSIGNMENTS
        // ---------------------------------------------

        foreach ($existingAssignments as $existing) {

            if (in_array($existing->id, $matchedAssignments, true)) {
                continue;
            }

            // Preserve assignments with grade history.
            if ($existing->gradeRecords()->exists()) {
                continue;
            }

            $existing->delete();
        }

        // ---------------------------------------------
        // UPDATE OR CREATE ASSIGNMENTS
        // ---------------------------------------------

        foreach ($resolvedAssignments as $resolved) {

            $assignment = $resolved['data'];
            $existing = $resolved['existing'];

            if ($existing) {

                // Lock assignments with grade records.
                if ($existing->gradeRecords()->exists()) {

                    $matchesExisting =
                        $existing->grade_level === $assignment['grade_level']
                        && $existing->subject === $assignment['subject']
                        && ($existing->component ?? 'General') === $assignment['component'];

                    if (!$matchesExisting) {
                        throw ValidationException::withMessages([
                            'assignments' =>
                                'This assignment already has grade records and cannot be changed. Keep its original grade level, subject, and component.',
                        ]);
                    }

                    continue;
                }

                // Safe to update when no grade records exist.
                $existing->update([
                    'grade_level' => $assignment['grade_level'],
                    'subject' => $assignment['subject'],
                    'component' => $assignment['component'],
                    'school_year' => $schoolYear,
                ]);

                continue;
            }

            // Create a new assignment.
            $faculty->teachingAssignments()->create([
                'grade_level' => $assignment['grade_level'],
                'subject' => $assignment['subject'],
                'component' => $assignment['component'],
                'school_year' => $schoolYear,
            ]);
        }
    }
}
