<?php



namespace App\Http\Controllers;



use App\Models\Faculty;

use App\Models\User;

use App\Models\AdvisoryAssignment;

use App\Models\TeachingAssignment;

use App\Services\PersonRecordService;

use App\Services\SchoolCsvSnapshotService;

use App\Services\AccountSetupService;
use App\Services\ActivityRecorder;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Hash;

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




    private function validateTeachingAssignmentConflicts(

        array $assignments,

        string $schoolYear,

        ?int $facultyId = null

    ): void {

        $seen = [];



        foreach ($assignments as $assignment) {

            $gradeLevel = $assignment['grade_level'];

            $subject = $assignment['subject'];

            $component = $assignment['component'];



            $key = implode('|', [

                $gradeLevel,

                $subject,

                $component,

                $schoolYear,

            ]);



            // Prevent duplicate combinations within the submitted form.

            if (isset($seen[$key])) {

                throw ValidationException::withMessages([

                    'assignments' =>

                        "Duplicate assignment detected: {$gradeLevel} - {$subject} - {$component}.",

                ]);

            }



            $seen[$key] = true;



            // Check whether another faculty already owns this assignment.

            $query = TeachingAssignment::where('grade_level', $gradeLevel)

                ->where('subject', $subject)

                ->where('component', $component)

                ->where('school_year', $schoolYear);



            if ($facultyId !== null) {

                $query->where('faculty_id', '!=', $facultyId);

            }



            if ($query->exists()) {

                throw ValidationException::withMessages([

                    'assignments' =>

                        "{$gradeLevel} - {$subject} - {$component} is already assigned to another faculty member for {$schoolYear}.",

                ]);

            }

        }

    }




    public function store(Request $request, AccountSetupService $accountSetup, ActivityRecorder $audit)
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

            'assignments.*.component' => [

                'required',

                Rule::in([

                    'General',

                    'Music and Arts',

                    'PE and Health',

                ]),

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



        $assignments = $validated['assignments'] ?? [];



        $role = $validated['teacher'] && $validated['adviser']

            ? 'teacher_adviser'

            : ($validated['adviser'] ? 'adviser' : 'teacher');



        $faculty = DB::transaction(function () use ($validated, $assignments, $role, $audit) {

            // Check teaching assignment conflicts before creating records.

            if ($validated['teacher']) {

                $this->validateTeachingAssignmentConflicts(

                    $assignments,

                    $validated['school_year']

                );

            }



            // Check adviser assignment for the selected school year.

            if ($validated['adviser']) {

                $alreadyAssigned = AdvisoryAssignment::where(

                    'grade_level',

                    $validated['advisory_grade']

                )

                    ->where('school_year', $validated['school_year'])

                    ->exists();



                if ($alreadyAssigned) {

                    throw ValidationException::withMessages([

                        'advisory_grade' =>

                            "An adviser is already assigned to {$validated['advisory_grade']} for {$validated['school_year']}. Only one adviser is allowed per grade level per school year.",

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



            // Save teaching assignments.

            if ($validated['teacher']) {

                foreach ($assignments as $assignment) {

                    $faculty->teachingAssignments()->create([

                        'grade_level' => $assignment['grade_level'],

                        'subject' => $assignment['subject'],

                        'component' => $assignment['component'],

                        'school_year' => $validated['school_year'],

                    ]);

                }

            }



            // Save advisory assignment.

            if ($validated['adviser']) {

                $faculty->advisoryAssignments()->create([

                    'grade_level' => $validated['advisory_grade'],

                    'school_year' => $validated['school_year'],

                ]);

            }



            $faculty->load(['teachingAssignments', 'advisoryAssignments']);
            $audit->record(
                'create',
                'faculty',
                (string) $faculty->faculty_id,
                null,
                $this->facultyAuditSnapshot($faculty),
                ['source' => 'faculty_management']
            );

            return $faculty;

        });



        // Send account setup email after the transaction succeeds.

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




    public function update(

        Request $request,

        int $id,

        AccountSetupService $accountSetup,
        ActivityRecorder $audit

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

                Rule::unique('faculty', 'email')->ignore($faculty->id),

                Rule::unique('users', 'email')->ignore($faculty->user_id),

            ],

            'teacher' => ['required', 'boolean'],

            'adviser' => ['required', 'boolean'],



            'assignments' => ['nullable', 'array'],

            'assignments.*.id' => [

                'nullable',

                'integer',

                'distinct',

            ],

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

            'assignments.*.component' => [

                'required',

                Rule::in([

                    'General',

                    'Music and Arts',

                    'PE and Health',

                ]),

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



        $assignments = $validated['assignments'] ?? [];



        $role = $validated['teacher'] && $validated['adviser']

            ? 'teacher_adviser'

            : ($validated['adviser'] ? 'adviser' : 'teacher');



        $setupUser = null;



        $faculty = DB::transaction(function () use ($id, $validated, $assignments, $role, &$setupUser, $audit) {

            $faculty = Faculty::whereKey($id)

                ->lockForUpdate()

                ->firstOrFail();

            $faculty->load(['teachingAssignments', 'advisoryAssignments']);
            $before = $this->facultyAuditSnapshot($faculty);

            $isUnassignedImport = !$faculty->user_id && !$faculty->role;



            $account = $faculty->user_id

                ? User::withTrashed()->find($faculty->user_id)

                : null;



            if (!$isUnassignedImport) {

                if (!$account || $account->trashed()) {

                    throw ValidationException::withMessages([

                        'email' =>

                            'The linked login account is unavailable. Repair or restore the account before editing this Faculty.',

                    ]);

                }

            }



            // Validate submitted assignment IDs.

            $currentAssignments = TeachingAssignment::where(

                'faculty_id',

                $faculty->id

            )

                ->where('school_year', $validated['school_year'])

                ->lockForUpdate()

                ->get();



            $currentAssignmentIds = $currentAssignments

                ->pluck('id')

                ->map(fn($assignmentId) => (int) $assignmentId)

                ->all();



            foreach ($assignments as $assignment) {

                if (!empty($assignment['id'])) {

                    $submittedId = (int) $assignment['id'];



                    if (!in_array($submittedId, $currentAssignmentIds, true)) {

                        throw ValidationException::withMessages([

                            'assignments' =>

                                'An invalid teaching assignment was submitted. Please refresh the Faculty form and try again.',

                        ]);

                    }

                }

            }



            // Check duplicate assignment combinations.

            if ($validated['teacher']) {

                $this->validateTeachingAssignmentConflicts(

                    $assignments,

                    $validated['school_year'],

                    $faculty->id

                );

            }



            // Check adviser assignment for this school year.

            if ($validated['adviser']) {

                $alreadyAssigned = AdvisoryAssignment::where(

                    'grade_level',

                    $validated['advisory_grade']

                )

                    ->where('school_year', $validated['school_year'])

                    ->where('faculty_id', '!=', $faculty->id)

                    ->exists();



                if ($alreadyAssigned) {

                    throw ValidationException::withMessages([

                        'advisory_grade' =>

                            "An adviser is already assigned to {$validated['advisory_grade']} for {$validated['school_year']}. Only one adviser is allowed per grade level per school year.",

                    ]);

                }

            }



            $name = trim(

                $validated['first_name'] . ' ' .

                ($validated['middle_name'] ?? '') . ' ' .

                $validated['last_name']

            );



            // Update Faculty details.

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




            $submittedIds = [];



            if ($validated['teacher']) {

                foreach ($assignments as $assignment) {

                    if (!empty($assignment['id'])) {

                        // Update an existing assignment owned by this faculty.

                        $teachingAssignment = TeachingAssignment::where(

                            'faculty_id',

                            $faculty->id

                        )

                            ->where('school_year', $validated['school_year'])

                            ->whereKey($assignment['id'])

                            ->firstOrFail();



                        $teachingAssignment->update([

                            'grade_level' => $assignment['grade_level'],

                            'subject' => $assignment['subject'],

                            'component' => $assignment['component'],

                        ]);



                        $submittedIds[] = $teachingAssignment->id;

                    } else {

                        // Create a new assignment.

                        $teachingAssignment = $faculty->teachingAssignments()->create([

                            'grade_level' => $assignment['grade_level'],

                            'subject' => $assignment['subject'],

                            'component' => $assignment['component'],

                            'school_year' => $validated['school_year'],

                        ]);



                        $submittedIds[] = $teachingAssignment->id;

                    }

                }

            }



            // Find current-year assignments omitted from the submitted form.

            $omittedAssignments = $currentAssignments->filter(

                fn($assignment) => !in_array(

                    (int) $assignment->id,

                    $submittedIds,

                    true

                )

            );



            foreach ($omittedAssignments as $omittedAssignment) {

                // Never remove an assignment that already has grade records.

                if ($omittedAssignment->gradeRecords()->exists()) {

                    throw ValidationException::withMessages([

                        'assignments' =>

                            "The assignment {$omittedAssignment->grade_level} - {$omittedAssignment->subject} - {$omittedAssignment->component} cannot be removed because it already has linked grade records. Preserve the assignment to protect existing academic records.",

                    ]);

                }

            }



            // Delete only omitted assignments with no linked grade records.

            foreach ($omittedAssignments as $omittedAssignment) {

                $omittedAssignment->delete();

            }




            $currentAdvisoryAssignments = AdvisoryAssignment::where(

                'faculty_id',

                $faculty->id

            )

                ->where('school_year', $validated['school_year'])

                ->get();



            if ($validated['adviser']) {

                $currentAdvisory = $currentAdvisoryAssignments->first();



                if ($currentAdvisory) {

                    $currentAdvisory->update([

                        'grade_level' => $validated['advisory_grade'],

                    ]);



                    // Remove only extra current-year advisory entries.

                    foreach ($currentAdvisoryAssignments->skip(1) as $extraAdvisory) {

                        $extraAdvisory->delete();

                    }

                } else {

                    $faculty->advisoryAssignments()->create([

                        'grade_level' => $validated['advisory_grade'],

                        'school_year' => $validated['school_year'],

                    ]);

                }

            } else {

                // Remove only this faculty's advisory assignment for this year.

                foreach ($currentAdvisoryAssignments as $advisoryAssignment) {

                    $advisoryAssignment->delete();

                }

            }



            // Create a login account only for an imported,

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



            $faculty = $faculty->fresh()->load([
                'teachingAssignments',
                'advisoryAssignments',
            ]);

            $after = $this->facultyAuditSnapshot($faculty);
            if ($before !== $after) {
                $audit->record(
                    'update',
                    'faculty',
                    (string) $faculty->faculty_id,
                    $before,
                    $after,
                    ['source' => 'faculty_management']
                );
            }

            return $faculty;

        });



        // Send setup email only when a new login account was created.

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


    /** Build a safe before/after snapshot for Faculty audit logs. */
    private function facultyAuditSnapshot(Faculty $faculty): array
    {
        return [
            'faculty' => $faculty->only([
                'faculty_id',
                'first_name',
                'middle_name',
                'last_name',
                'email',
                'role',
                'status',
            ]),
            'teaching_assignments' => $faculty->teachingAssignments
                ->map(fn($item) => $item->only([
                    'grade_level',
                    'subject',
                    'component',
                    'school_year',
                ]))
                ->values()
                ->all(),
            'advisory_assignments' => $faculty->advisoryAssignments
                ->map(fn($item) => $item->only([
                    'grade_level',
                    'school_year',
                ]))
                ->values()
                ->all(),
        ];
    }

}
