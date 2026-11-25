<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class GradeLevelController extends Controller
{
    public function index()
    {
        // Get the currently active school year.
        $activeSchoolYear = DB::table('school_years')
            ->where('status', 'active')
            ->orderByDesc('id')
            ->first();

        $schoolYear = $activeSchoolYear->year_name ?? null;

        // Supported Junior High School grade levels.
        $gradeNumbers = [7, 8, 9, 10];

        $grades = [];

        foreach ($gradeNumbers as $number) {

            $gradeLabel = 'Grade ' . $number;

            // Count active students for Grade Details.
            $studentCount = DB::table('students')
                ->where('grade_level', $gradeLabel)
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->when($schoolYear, function ($query) use ($schoolYear) {
                    $query->where('school_year', $schoolYear);
                })
                ->count();

            // Count unique active teachers assigned to this grade.
            $teacherCount = 0;

            // Get the assigned adviser for this grade and school year.
            $adviser = 'Not assigned';

            if ($schoolYear) {

                $teacherCount = DB::table('teaching_assignments')
                    ->join(
                        'faculty',
                        'teaching_assignments.faculty_id',
                        '=',
                        'faculty.id'
                    )
                    ->where('teaching_assignments.grade_level', $gradeLabel)
                    ->where('teaching_assignments.school_year', $schoolYear)
                    ->where('faculty.status', 'active')
                    ->whereNull('faculty.deleted_at')
                    ->distinct('teaching_assignments.faculty_id')
                    ->count('teaching_assignments.faculty_id');

                $adviserRecord = DB::table('advisory_assignments')
                    ->join(
                        'faculty',
                        'advisory_assignments.faculty_id',
                        '=',
                        'faculty.id'
                    )
                    ->where('advisory_assignments.grade_level', $gradeLabel)
                    ->where('advisory_assignments.school_year', $schoolYear)
                    ->where('faculty.status', 'active')
                    ->whereNull('faculty.deleted_at')
                    ->select(
                        'faculty.first_name',
                        'faculty.middle_name',
                        'faculty.last_name'
                    )
                    ->orderBy('advisory_assignments.id')
                    ->first();

                if ($adviserRecord) {
                    $adviser = trim(
                        $adviserRecord->first_name . ' ' .
                        ($adviserRecord->middle_name
                            ? $adviserRecord->middle_name . ' '
                            : '') .
                        $adviserRecord->last_name
                    );
                }
            }

            // Get subjects assigned to this grade level.
            $subjects = DB::table('grade_level_subjects')
                ->join(
                    'subjects',
                    'grade_level_subjects.subject_id',
                    '=',
                    'subjects.id'
                )
                ->where('grade_level_subjects.grade_level', $number)
                ->where('subjects.status', 'active')
                ->select(
                    'subjects.id',
                    'subjects.subject_name',
                    'subjects.subject_code'
                )
                ->orderBy('subjects.subject_name')
                ->get()
                ->flatMap(function ($subject) use ($number, $schoolYear, $gradeLabel) {

                    // Get ALL active teacher assignments for this subject,
                    // grade level, and school year.
                    $assignments = collect();

                    if ($schoolYear) {
                        $assignments = DB::table('teaching_assignments')
                            ->join(
                                'faculty',
                                'teaching_assignments.faculty_id',
                                '=',
                                'faculty.id'
                            )
                            ->where(
                                'teaching_assignments.grade_level',
                                $gradeLabel
                            )
                            ->where(
                                'teaching_assignments.school_year',
                                $schoolYear
                            )
                            ->where(
                                'teaching_assignments.subject',
                                $subject->subject_name
                            )
                            ->where('faculty.status', 'active')
                            ->whereNull('faculty.deleted_at')
                            ->select(
                                'teaching_assignments.id',
                                'teaching_assignments.component',
                                'faculty.first_name',
                                'faculty.middle_name',
                                'faculty.last_name'
                            )
                            ->orderBy('teaching_assignments.component')
                            ->get();
                    }

                    // If no teacher is assigned, still display the subject.
                    if ($assignments->isEmpty()) {
                        return [
                            [
                                'id' => $subject->id,
                                'subject_name' => $subject->subject_name,
                                'subject_code' => $subject->subject_code,
                                'component' => null,
                                'assignment_id' => null,
                                'teacher' => null,
                                'gradebook_url' => null,
                            ]
                        ];
                    }

                    // Create one entry for each exact teaching assignment.
                    return $assignments->map(function ($assignment) use ($subject, $number, $schoolYear) {

                        $teacherName = trim(
                            $assignment->first_name . ' ' .
                            ($assignment->middle_name
                                ? $assignment->middle_name . ' '
                                : '') .
                            $assignment->last_name
                        );

                        return [
                            'id' => $subject->id,
                            'subject_name' => $subject->subject_name,
                            'subject_code' => $subject->subject_code,
                            'component' => $assignment->component,
                            'assignment_id' => $assignment->id,
                            'teacher' => $teacherName,

                            // Pass the exact teaching assignment ID.
                            'gradebook_url' => route('admin.gradebook', [
                                'code' => $subject->subject_code,
                                'grade' => $number,
                                'school_year' => $schoolYear,
                                'assignment_id' => $assignment->id,
                            ]),
                        ];
                    });
                })
                ->values();

            // Store grade-level information.
            $grades[] = [
                'number' => $number,
                'grade' => $gradeLabel,
                'students' => $studentCount,
                'teachers' => $teacherCount,
                'adviser' => $adviser,
                'subjects' => $subjects,
            ];
        }

        return view('admin.grade-level', compact(
            'grades',
            'activeSchoolYear',
            'schoolYear'
        ));
    }
}
