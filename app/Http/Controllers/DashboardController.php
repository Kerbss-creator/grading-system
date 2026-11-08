<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use App\Models\Student;
use App\Models\AdvisoryAssignment;
use App\Models\GradeRecord;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{


    public function admin()
    {
        // Get total student records.
        $totalStudents = Student::count();

        // Get total faculty records.
        $totalFaculties = Faculty::count();

        // Get the 3 latest announcements for the dashboard.
        $latestAnnouncements = \App\Models\Announcement::latest()
            ->take(3)
            ->get();

        // Get all announcements for the View All modal.
        $allAnnouncements = \App\Models\Announcement::latest()
            ->get();

        return view('admin.dashboard-admin', compact(
            'totalStudents',
            'totalFaculties',
            'latestAnnouncements',
            'allAnnouncements'
        ));
    }


    public function teacher()
    {
        return view('teacher.dashboard');
    }



    public function adviser(Request $request)
    {
        $schoolYear = '2026-2027';

        // Get selected term.
        $term = (int) $request->query('term', 1);

        if (!in_array($term, [1, 2, 3])) {
            $term = 1;
        }

        // Get the logged-in adviser's faculty record.
        $faculty = Faculty::where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        // Default values.
        $assignedClasses = collect();
        $totalStudents = 0;
        $pendingReviews = 0;
        $forwardedToAdmin = 0;
        $recentSubmissions = collect();

        if ($faculty) {

            // Get adviser's assigned grade levels.
            $advisoryAssignments = AdvisoryAssignment::where('faculty_id', $faculty->id)
                ->where(function ($query) use ($schoolYear) {
                    $query->where('school_year', $schoolYear)
                        ->orWhereNull('school_year');
                })
                ->orderBy('grade_level')
                ->get();

            $studentIds = collect();

            // Get students and subjects for each assigned grade level.
            $assignedClasses = $advisoryAssignments->map(
                function ($assignment) use ($schoolYear, $term, &$studentIds) {

                    // Get active students under this grade level.
                    $classStudentIds = Student::where('grade_level', $assignment->grade_level)
                        ->where('school_year', $schoolYear)
                        ->where('status', 'active')
                        ->pluck('id');

                    $studentCount = $classStudentIds->count();

                    $assignment->student_count = $studentCount;

                    $studentIds = $studentIds->merge($classStudentIds);

                    // Get subjects assigned to this grade level.
                    $teachingAssignments = TeachingAssignment::with('faculty')
                        ->where('grade_level', $assignment->grade_level)
                        ->get();

                    // Calculate submission progress per subject.
                    $assignment->subjectProgress = $teachingAssignments->map(
                        function ($teachingAssignment) use ($classStudentIds, $studentCount, $schoolYear, $term) {

                            $submittedCount = 0;

                            if ($studentCount > 0) {
                                $submittedCount = GradeRecord::where(
                                    'teaching_assignment_id',
                                    $teachingAssignment->id
                                )
                                    ->where('school_year', $schoolYear)
                                    ->where('term', $term)
                                    ->where('status', 'submitted')
                                    ->whereNotNull('submitted_at')
                                    ->whereIn('student_id', $classStudentIds)
                                    ->distinct()
                                    ->count('student_id');
                            }

                            // Determine submission status.
                            if ($studentCount > 0 && $submittedCount >= $studentCount) {
                                $status = 'Submitted';
                            } elseif ($submittedCount > 0) {
                                $status = 'Partial';
                            } else {
                                $status = 'Not Submitted';
                            }

                            return (object) [
                                'teachingAssignment' => $teachingAssignment,
                                'subject' => $teachingAssignment->subject,
                                'faculty' => $teachingAssignment->faculty,
                                'total_students' => $studentCount,
                                'submitted_count' => $submittedCount,
                                'status' => $status,
                            ];
                        }
                    );

                    // Calculate grade-level submission progress.
                    $totalSubjects = $assignment->subjectProgress->count();

                    $assignment->total_subjects = $totalSubjects;

                    $assignment->submitted_subjects = $assignment->subjectProgress
                        ->where('status', 'Submitted')
                        ->count();

                    $assignment->partial_subjects = $assignment->subjectProgress
                        ->where('status', 'Partial')
                        ->count();

                    $assignment->not_submitted_subjects = $assignment->subjectProgress
                        ->where('status', 'Not Submitted')
                        ->count();

                    $assignment->progress_percentage = $totalSubjects > 0
                        ? round(
                            ($assignment->submitted_subjects / $totalSubjects) * 100
                        )
                        : 0;

                    return $assignment;
                }
            );

            // Count unique students across the adviser's assigned grade levels.
            $studentIds = $studentIds->unique()->values();

            $totalStudents = $studentIds->count();

            // Get submitted records for the selected school year and term.
            if ($studentIds->isNotEmpty()) {

                $submittedRecords = GradeRecord::with('teachingAssignment.faculty')
                    ->where('school_year', $schoolYear)
                    ->where('term', $term)
                    ->where('status', 'submitted')
                    ->whereNotNull('submitted_at')
                    ->whereIn('student_id', $studentIds)
                    ->get();

                // Group records into individual teacher submissions.
                $submissionGroups = $submittedRecords->groupBy(
                    function ($record) {
                        return $record->teaching_assignment_id
                            . '|' . $record->school_year
                            . '|' . $record->term;
                    }
                );

                $pendingReviews = $submissionGroups->count();

                // Get the five most recent submissions.
                $recentSubmissions = $submissionGroups
                    ->map(function ($records) {

                        $latestRecord = $records->sortByDesc('submitted_at')->first();

                        return (object) [
                            'teachingAssignment' => $latestRecord->teachingAssignment,
                            'school_year' => $latestRecord->school_year,
                            'term' => $latestRecord->term,
                            'submitted_at' => $records->max('submitted_at'),
                            'status' => 'Pending Review',
                        ];
                    })
                    ->sortByDesc('submitted_at')
                    ->take(5)
                    ->values();
            }
        }

        return view('adviser.dashboard', compact(
            'faculty',
            'schoolYear',
            'term',
            'assignedClasses',
            'totalStudents',
            'pendingReviews',
            'forwardedToAdmin',
            'recentSubmissions'
        ));
    }



    public function student()
    {
        return view('student.dashboard');
    }
}
