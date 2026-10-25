<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index()
    {
        return view('admin.student-management');
    }

    public function show($id)
    {
       
        // para makita ang detalye ng isang student.
    }

    public function create()
    {
        return view('admin.students-create');
    }

    public function store(Request $request)
    {
       
        $validated = $request->validate([
            'student_id' => 'required|unique:students,student_id',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'grade_level' => 'required|string|max:255',
            'section' => 'nullable|string|max:255',
            'sex' => 'required|string|max:255',
            'school_year' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'status' => 'required|in:active,inactive',
        ]);

        DB::transaction(function () use ($validated) {
            // Create the User record for authentication
            $user = User::create([
                'name' => trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? '')),
                'email' => $validated['email'] ?? ($validated['student_id'] ?? 'student') . '@forbesacademy.edu,ph',
                'password' => Hash::make('password123'),
                'role' => 'student',
            ]);

            // Create the Student record
            Student::create([
                'student_id' => $validated['student_id'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'grade_level' => $validated['grade_level'],
                'section' => $validated['section'] ?? null,
                'sex' => $validated['sex'],
                'school_year' => $validated['school_year'],
                'email' => $validated['email'] ?? null,
                'status' => $validated['status'],
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Student added successfully.', 'student' => $student]);
    }

    public function edit($id)
    {
        $student = Student::findOrFail($id);
        // para i-edit ang student record.
    }

    public function update(Request $request, $id)
    {
        
        $student = Student::findOrFail($id);
        
        $validated = $request->validate([
            'student_id' => 'required|unique:students,student_id,' . $id,
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'grade_level' => 'required|string|max:255',
            'section' => 'nullable|string|max:255',
            'sex' => 'required|string|max:255',
            'school_year' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $student->id,
            'status' => 'required|in:active,inactive',
        ]);

        $student->update([
            'student_id' => $validated['student_id'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'grade_level' => $validated['grade_level'],
            'section' => $validated['section'] ?? null,
            'sex' => $validated['sex'],
            'school_year' => $validated['school_year'],
            'email' => $validated['email'] ?? null,
            'status' => $validated['status'],
        ]);

        return response()->json(['success' => true, 'message' => 'Student updated successfully.']);
    
    }

    public function destroy($id)
    {
        
        $student = Student::findOrFail($id);
        $student->delete();

        return response()->json(['success' => true, 'message' => 'Student deleted successfully.']);
    }

        public function toggleStatus($id)
    {
        $student = Student::findOrFail($id);
        $student->status = $student->status === 'active' ? 'inactive' : 'active';
        $student->save();

        return response()->json([
            'success' => true,
            'status' => $student->status,
            'message' => 'Student status updated.'
        ]);
    }

}
