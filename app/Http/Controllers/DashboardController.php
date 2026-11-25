<?php

namespace App\Http\Controllers;

use App\Models\Faculty;
use App\Models\Student;
use App\Models\AdvisoryAssignment;
use App\Models\GradeRecord;
use App\Models\TeachingAssignment;
use App\Models\SchoolYear;
use App\Models\ActivityLog;
use App\Models\Announcement;
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
        $latestAnnouncements = Announcement::latest()
            ->take(3)
            ->get();

        // Get all announcements for the View All modal.
        $allAnnouncements = Announcement::latest()
            ->get();

        return view('admin.dashboard-admin', compact(
            'totalStudents',
            'totalFaculties',
            'latestAnnouncements',
            'allAnnouncements'
        ));
    }

    /**
     * Display the dynamic teacher dashboard.
     */
    public function teacher()
    {
        // Get the active school year and current term.
        $activeSchoolYear = SchoolYear::active();

        $schoolYear = $activeSchoolYear?->year_name;
        $term = $activeSchoolYear?->current_term;

        // Default dashboard values.
        $faculty = null;
        $assignedClasses = collect();

        $totalClasses = 0;
        $totalStudents = 0;
        $pendingGrades = 0;
        $completedGrades = 0;
        $submittedGrades = 0;

        $overallCompletionPercentage = 0;

        $recentActivities = collect();
        $announcements = collect();

        // Get the logged-in teacher's active faculty record.
        $faculty = Faculty::where('user_id', auth()->id())
            ->where('status', 'active')
            ->first();

        if ($faculty && $activeSchoolYear && in_array($term, [1, 2, 3], true)) {

            // Get only this teacher's assignments for the active school year.
            $assignedClasses = $faculty->teachingAssignments()
                ->where('school_year', $schoolYear)
                ->orderBy('grade_level')
                ->orderBy('subject')
                ->get();

            $totalClasses = $assignedClasses->count();

            $uniqueStudentIds = collect();

            $requiredAssessmentCodes = [
                'WW1',
                'WW2',
                'WW3',
                'WW4',
                'PT1',
                'PT2',
                'SA',
                'SB',
                'SC',
                'TE',
            ];

            // Calculate grade completion for every teaching assignment.
            foreach ($assignedClasses as $assignment) {

                // Get active students for this assignment's grade level.
                $students = Student::where(
                    'grade_level',
                    $assignment->grade_level
                )
                    ->where('school_year', $schoolYear)
                    ->where('status', 'active')
                    ->get(['id']);

                $studentIds = $students->pluck('id');

                // Merge IDs to prevent duplicate overall student counts.
                $uniqueStudentIds = $uniqueStudentIds->merge($studentIds);

                $assignment->total_students = $studentIds->count();

                // Get grade records for this assignment and active term.
                $gradeRecords = GradeRecord::with('assessmentScores')
                    ->where('teaching_assignment_id', $assignment->id)
                    ->where('school_year', $schoolYear)
                    ->where('term', $term)
                    ->whereIn('student_id', $studentIds)
                    ->get()
                    ->keyBy('student_id');

                $assignmentCompleted = 0;
                $assignmentSubmitted = 0;

                foreach ($studentIds as $studentId) {

                    $record = $gradeRecords->get($studentId);

                    $isComplete = false;

                    if ($record) {
                        $scores = $record->assessmentScores
                            ->keyBy('assessment_code');

                        $isComplete = collect($requiredAssessmentCodes)
                            ->every(function ($code) use ($scores) {
                                $assessment = $scores->get($code);

                                return $assessment
                                    && $assessment->score !== null
                                    && (float) $assessment->highest_possible_score > 0;
                            });

                        if (
                            $record->status === 'submitted'
                            && $record->submitted_at !== null
                        ) {
                            $assignmentSubmitted++;
                        }
                    }

                    if ($isComplete) {
                        $assignmentCompleted++;
                    }
                }

                $assignment->completed_students = $assignmentCompleted;

                $assignment->pending_students =
                    $assignment->total_students - $assignmentCompleted;

                $assignment->submitted_students = $assignmentSubmitted;

                $assignment->completion_percentage =
                    $assignment->total_students > 0
                    ? round(
                        ($assignmentCompleted / $assignment->total_students) * 100,
                        2
                    )
                    : 0;

                // Update overall counts.
                $completedGrades += $assignmentCompleted;
                $pendingGrades += $assignment->pending_students;
                $submittedGrades += $assignmentSubmitted;
            }

            // Count each student only once across all assigned classes.
            $totalStudents = $uniqueStudentIds->unique()->count();

            // Calculate overall assessment completion percentage.
            $totalAssignedStudentSubjects = $completedGrades + $pendingGrades;

            $overallCompletionPercentage = $totalAssignedStudentSubjects > 0
                ? round(
                    ($completedGrades / $totalAssignedStudentSubjects) * 100,
                    2
                )
                : 0;

            // Get this teacher's actual activity logs.
            $recentActivities = ActivityLog::where(
                'actor_id',
                auth()->id()
            )
                ->latest('created_at')
                ->take(5)
                ->get();

        }

        // Get announcements intended for all users and teachers.
        $announcements = Announcement::whereIn(
            'audience',
            ['all', 'teachers']
        )
            ->latest()
            ->take(5)
            ->get();

        return view('teacher.dashboard', compact(
            'faculty',
            'schoolYear',
            'term',
            'assignedClasses',
            'totalClasses',
            'totalStudents',
            'pendingGrades',
            'completedGrades',
            'submittedGrades',
            'overallCompletionPercentage',
            'recentActivities',
            'announcements'
        ));
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
