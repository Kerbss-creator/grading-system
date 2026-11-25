<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Faculty;
use App\Models\User;
use App\Models\Announcement;
use App\Models\GradeRecord;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Available school years from students and grade records.
        $schoolYears = collect()
            ->merge(
                Student::whereNotNull('school_year')
                    ->distinct()
                    ->pluck('school_year')
            )
            ->merge(
                GradeRecord::whereNotNull('school_year')
                    ->distinct()
                    ->pluck('school_year')
            )
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        if ($schoolYears->isEmpty()) {
            $schoolYears = collect(['2026-2027']);
        }

        $schoolYear = $request->query('school_year', '2026-2027');

        // Only allow school years that actually exist.
        if (!$schoolYears->contains($schoolYear)) {
            $schoolYear = $schoolYears->first();
        }

        // Academic term selector.
        $term = (int) $request->query('term', 1);

        if (!in_array($term, [1, 2, 3], true)) {
            $term = 1;
        }

        $termOptions = [
            1 => '1st Term',
            2 => '2nd Term',
            3 => '3rd Term',
        ];

        // Active students for the selected school year.
        $totalStudents = Student::where('status', 'active')
            ->where('school_year', $schoolYear)
            ->count();

        // All active faculty members.
        $totalFaculties = Faculty::where('status', 'active')
            ->count();

        // Active subject teachers, including teacher-advisers.
        $totalTeachers = Faculty::where('status', 'active')
            ->whereIn('role', ['teacher', 'teacher_adviser'])
            ->count();

        // Active advisers, including teacher-advisers.
        $totalAdvisers = Faculty::where('status', 'active')
            ->whereIn('role', ['adviser', 'teacher_adviser'])
            ->count();

        // Total administrator accounts.
        $totalAdmins = User::where('role', 'admin')->count();

        // Active subjects from the dedicated subjects table.
        $totalSubjects = DB::table('subjects')
            ->where('status', 'active')
            ->count();

        // Total faculty teaching assignments.
        $totalClasses = DB::table('teaching_assignments')->count();

        // Active student distribution by grade level.
        $studentsByGrade = Student::where('status', 'active')
            ->where('school_year', $schoolYear)
            ->selectRaw('grade_level, COUNT(*) as count')
            ->groupBy('grade_level')
            ->orderBy('grade_level')
            ->get();

        // Number of grade levels with active students.
        // This is not a count of class sections.
        $totalGradeLevels = $studentsByGrade->count();

        // Recently added active students.
        $recentStudents = Student::where('status', 'active')
            ->where('school_year', $schoolYear)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        /*
         * Grade submission progress.
         * One batch = one teaching assignment + school year + term.
         */

        $gradeRecords = GradeRecord::query()
            ->join(
                'teaching_assignments',
                'teaching_assignments.id',
                '=',
                'grade_records.teaching_assignment_id'
            )
            ->where('grade_records.school_year', $schoolYear)
            ->where('grade_records.term', $term)
            ->where(function ($query) {
                $query->whereIn('grade_records.status', [
                    'forwarded_to_admin',
                    'approved'
                ])->orWhere(function ($query) {
                    $query->where('grade_records.status', 'returned')
                        ->whereNotNull('grade_records.admin_return_reason');
                });
            })
            ->get([
                'teaching_assignments.grade_level',
                'grade_records.teaching_assignment_id',
                'grade_records.school_year',
                'grade_records.term',
                'grade_records.status',
                'grade_records.admin_return_reason'
            ]);

        $submissionDetails = $gradeRecords
            ->groupBy(function ($record) {
                return $record->grade_level . '|' .
                    $record->teaching_assignment_id . '|' .
                    $record->school_year . '|' .
                    $record->term;
            })
            ->map(function ($group) {
                $statuses = $group->pluck('status')->unique()->values();

                return [
                    'grade_level' => $group->first()->grade_level,
                    'status' => $statuses->count() === 1
                        ? $statuses->first()
                        : 'inconsistent',
                ];
            });

        $submissions = $submissionDetails->pluck('status');

        $pendingGrades = $submissions
            ->filter(fn($status) => $status === 'forwarded_to_admin')
            ->count();

        $approvedGrades = $submissions
            ->filter(fn($status) => $status === 'approved')
            ->count();

        $returnedGrades = $submissions
            ->filter(fn($status) => $status === 'returned')
            ->count();

        $gradeProgressByLevel = $submissionDetails
            ->groupBy('grade_level')
            ->map(function ($group) {
                return [
                    'pending' => $group->where('status', 'forwarded_to_admin')->count(),
                    'approved' => $group->where('status', 'approved')->count(),
                    'returned' => $group->where('status', 'returned')->count(),
                    'total' => $group->count(),
                ];
            });


        $pendingGrades = $submissions
            ->filter(fn($status) => $status === 'forwarded_to_admin')
            ->count();

        $approvedGrades = $submissions
            ->filter(fn($status) => $status === 'approved')
            ->count();

        $returnedGrades = $submissions
            ->filter(fn($status) => $status === 'returned')
            ->count();

        // Recent actual system activities.
        $recentActivities = \App\Models\ActivityLog::with([])
            ->latest('created_at')
            ->limit(5)
            ->get();

        // Announcements.
        $latestAnnouncements = Announcement::latest()
            ->take(3)
            ->get();

        $allAnnouncements = Announcement::latest()->get();

        return view('admin.dashboard-admin', compact(
            'schoolYears',
            'schoolYear',
            'term',
            'termOptions',
            'totalStudents',
            'totalFaculties',
            'totalTeachers',
            'totalSubjects',
            'totalGradeLevels',
            'totalAdvisers',
            'totalAdmins',
            'studentsByGrade',
            'recentStudents',
            'pendingGrades',
            'approvedGrades',
            'returnedGrades',
            'submissions',
            'submissionDetails',
            'gradeProgressByLevel',
            'recentActivities',
            'latestAnnouncements',
            'allAnnouncements'
        ));
    }
}
