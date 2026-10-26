<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Try to find the student record linked to this user's email
        $student = Student::where('email', $user->email)->first();

        // Fallback if the student profile hasn't been fully created by admin yet
        if (!$student) {
            $student = (object) [
                'student_id' => 'PENDING',
                'first_name' => explode(' ', $user->name)[0],
                'last_name' => explode(' ', $user->name)[1] ?? '',
                'grade_level' => 'N/A',
                'section' => 'N/A',
                'school_year' => '2026 - 2027'
            ];
        }

        return view('student.dashboard', compact('student', 'user'));
    }
}