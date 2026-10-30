<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use Illuminate\Http\Request;
use App\Models\TeachingAssignment;
use App\Models\AdvisoryAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
                'message' => 'Pumili ng Teacher, Adviser, o pareho.',
            ], 422);
        }

        if ($validated['adviser'] && empty($validated['advisory_grade'])) {
            return response()->json([
                'message' => 'Kailangan ang advisory grade level.'
            ], 422);
        }

        if ($validated['teacher'] && empty($validated['assignments'])) {
            return response()->json([
                'message' => 'Magdagdag ng kahit isang teaching assignment.',
            ], 422);
        }

        // Tukuyin ang login role mula sa faculty assignments.
        $role = $validated['teacher'] && $validated['adviser']
            ? 'teacher_adviser'
            : ($validated['adviser'] ? 'adviser' : 'teacher');

        $faculty = DB::transaction(function () use ($validated, $role) {
            // Gumawa ng login account.
            $user = User::create([
                'name' => trim(
                    $validated['first_name'] . ' ' .
                    $validated['last_name']
                ),
                'email' => $validated['email'],
                'password' => Hash::make($validated['temporary_password'] ?? 'faculty123'),
                'role' => $role,
            ]);

            // I-link ang faculty profile sa login account.
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
            'message' => 'Faculty at login account ay matagumpay na nagawa.',
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
                'required', 'string', 'max:50',
                Rule::unique('faculty', 'faculty_id')->ignore($faculty->id),
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('faculty', 'email')->ignore($faculty->id),
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
                'message' => 'Pumili ng Teacher, Adviser, o pareho.',
            ], 422);
        }

        if ($validated['adviser'] && empty($validated['advisory_grade'])) {
            return response()->json([
                'message' => 'Kailangan ang advisory grade level.'
            ], 422);
        }

        if ($validated['teacher'] && empty($validated['assignments'])) {
            return response()->json([
                'message' => 'Magdagdag ng kahit isang teaching assignment.',
            ], 422);
        }



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
            'message' => 'Matagumpay na na-update ang faculty.',
            'faculty' => $faculty->fresh()->load([
                'teachingAssignments',
                'advisoryAssignments',
            ]),
        ]);
    }

    public function destroy(int $id)
    {
        $faculty = Faculty::findOrFail($id);
        $faculty->delete();

        return response()->json([
            'message' => 'Matagumpay na na-delete ang faculty.',
        ]);
    }
}

