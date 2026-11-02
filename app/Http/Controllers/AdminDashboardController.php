<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Subject;
use App\Models\SubjectOffering;
use App\Models\GradeRecord;
use App\Support\SystemOwner;

class AdminDashboardController extends Controller
{
    public function index()
    {

        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $activeStudents = Student::where('status', 'active')->count();
        $inactiveStudents = Student::where('status', 'inactive')->count();
        $activeTeachers = Teacher::where('status', 'active')->count();
        $inactiveTeachers = Teacher::where('status', 'inactive')->count();
        $activeAdmins = SystemOwner::visibleTo(User::where('role', 'admin')->where('is_active', true), request()->user())->count();
        $inactiveAdmins = SystemOwner::visibleTo(User::where('role', 'admin')->where('is_active', false), request()->user())->count();
        $totalAdmins = SystemOwner::visibleTo(User::where('role', 'admin'), request()->user())->count();

        $studentsByGrade = Student::selectRaw('grade_level, COUNT(*) as count')
            ->groupBy('grade_level')
            ->get()
            ->sortBy(function ($row) {
                $grade = strtolower(trim($row->grade_level));
                if ($grade === 'pre-kindergarten') return 0;
                if ($grade === 'kindergarten') return 1;
                if (preg_match('/(\d+)$/', $grade, $matches)) return (int) $matches[1] + 2;
                return 999;
            })->values();

        $totalSubjects = Subject::count();

        // No class model exists yet. Count only known grade/section combinations.
        $totalClasses = Student::whereNotNull('section')->where('section', '!=', '')
            ->get(['grade_level', 'section', 'school_year'])
            ->map(fn ($row) => $row->grade_level . '|' . $row->section . '|' . $row->school_year)->unique()->count();

        $recentStudents = Student::orderBy('created_at', 'desc')->limit(5)->get();

        $pendingGrades = GradeRecord::where('status', 'pending')->count();
        $approvedGrades = GradeRecord::where('status', 'approved')->count();
        $returnedGrades = GradeRecord::where('status', 'returned')->count();

        return view('admin.dashboard-admin', compact(
            'totalStudents',
            'totalTeachers',
            'activeStudents', 'inactiveStudents', 'activeTeachers', 'inactiveTeachers', 'activeAdmins', 'inactiveAdmins',
            'totalAdmins',
            'totalSubjects',
            'totalClasses',
            'studentsByGrade',
            'recentStudents',
            'pendingGrades', 'approvedGrades', 'returnedGrades'
        ));
    }
}
