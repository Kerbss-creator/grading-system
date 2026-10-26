<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::orderBy('created_at', 'desc')->get();
        return view('admin.teacher-management', compact('teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|unique:teachers,teacher_id',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            // Allow empty string, but validate format if not empty
            'email' => 'nullable|string|email|unique:users,email', 
            'subject' => 'required|string|max:255',
            'school_year' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        // If email is empty or null, generate a safe default
        $email = !empty($validated['email']) ? $validated['email'] : ($validated['teacher_id'] . '@forbes.edu');

        $teacher = DB::transaction(function () use ($validated, $email) {
            User::create([
                'name' => trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? '')),
                'email' => $email,
                'password' => Hash::make('password123'),
                'role' => 'teacher',
            ]);

            return Teacher::create([
                'teacher_id' => $validated['teacher_id'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'email' => $email,
                'subject' => $validated['subject'],
                'school_year' => $validated['school_year'],
                'status' => $validated['status'],
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Teacher added successfully.', 'teacher' => $teacher]);
    }

    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);
        
        // Find the linked User to ignore their ID during validation
        $linkedUser = User::where('email', $teacher->email)->first();
        $userIdToIgnore = $linkedUser ? $linkedUser->id : 0;

        $validated = $request->validate([
            'teacher_id' => 'required|unique:teachers,teacher_id,' . $id,
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|string|email|unique:users,email,' . $userIdToIgnore,
            'subject' => 'required|string|max:255',
            'school_year' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $email = !empty($validated['email']) ? $validated['email'] : $teacher->email;

        DB::transaction(function () use ($validated, $teacher, $email, $linkedUser) {
            $teacher->update([
                'teacher_id' => $validated['teacher_id'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'email' => $email,
                'subject' => $validated['subject'],
                'school_year' => $validated['school_year'],
                'status' => $validated['status'],
            ]);

            if ($linkedUser) {
                $linkedUser->update([
                    'name' => trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? '')),
                    'email' => $email,
                ]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Teacher updated successfully.']);
    }

    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        
        $linkedUser = User::where('email', $teacher->email)->first();
        if ($linkedUser) {
            $linkedUser->delete();
        }
        
        $teacher->delete();

        return response()->json(['success' => true, 'message' => 'Teacher deleted successfully.']);
    }

    public function toggleStatus($id)
    {
        $teacher = Teacher::findOrFail($id);
        $teacher->status = $teacher->status === 'active' ? 'inactive' : 'active';
        $teacher->save();

        return response()->json([
            'success' => true,
            'status' => $teacher->status,
            'message' => 'Teacher status updated.'
        ]);
    }
}