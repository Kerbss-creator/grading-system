<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Get total counts from database
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalAdmins = User::where('role', 'admin')->count();
        
        // Get students by grade level (useful for charts/lists later)
        $studentsByGrade = Student::selectRaw('grade_level, COUNT(*) as count')
            ->groupBy('grade_level')
            ->orderBy('grade_level')
            ->get();

        // Get total unique subjects from teachers
        $totalSubjects = Teacher::select('subject')->distinct()->count();

        // For now, estimate total classes based on teachers (can be updated when a Classes table is added)
        $totalClasses = Teacher::count(); 

        // Get recent students for a "Recently Added" section
        $recentStudents = Student::orderBy('created_at', 'desc')->limit(5)->get();

        // Placeholder for pending grades (update when Grades table is created)
        $pendingGrades = 0; 

        return view('admin.dashboard-admin', compact(
            'totalStudents',
            'totalTeachers',
            'totalAdmins',
            'totalSubjects',
            'totalClasses',
            'studentsByGrade',
            'recentStudents',
            'pendingGrades'
        ));
    }
}