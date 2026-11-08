<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Student;
use App\Models\TeachingAssignment;
use App\Models\AdvisoryAssignment;
use App\Models\GradeRecord;
use Illuminate\Http\Request;

class GradebookController extends Controller
{
    /**
     * Display the read-only Admin Gradebook.
     */
    public function index(Request $request)
    {
        // Validate the selected subject, grade, and school year.
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'grade' => ['required', 'integer', 'in:7,8,9,10'],
            'school_year' => ['nullable', 'string', 'max:20'],
        ]);

        $subject = Subject::where(
            'subject_code',
            $validated['code']
        )->firstOrFail();

        $grade = (int) $validated['grade'];
        $gradeLevel = 'Grade ' . $grade;
        $schoolYear = $validated['school_year'] ?? '2026-2027';

        // Get the teacher assigned to this subject and grade.
        $assignment = TeachingAssignment::with('faculty')
            ->where('subject', $subject->subject_name)
            ->where('grade_level', $gradeLevel)
            ->orderByDesc('id')
            ->first();

        // Get the adviser or advisers for this grade and school year.
        $advisers = AdvisoryAssignment::with('faculty')
            ->where('grade_level', $gradeLevel)
            ->where(function ($query) use ($schoolYear) {
                $query->where('school_year', $schoolYear)
                    ->orWhereNull('school_year');
            })
            ->get()
            ->pluck('faculty')
            ->filter()
            ->unique('id')
            ->values();

        // Get active students under the selected grade and school year.
        $students = Student::where('grade_level', $gradeLevel)
            ->where('school_year', $schoolYear)
            ->where('status', 'active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Load existing grade records without recalculating anything.
        $records = collect();

        if ($assignment && $students->isNotEmpty()) {
            $records = GradeRecord::with('assessmentScores')
                ->where('teaching_assignment_id', $assignment->id)
                ->where('school_year', $schoolYear)
                ->whereIn('term', [1, 2, 3])
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->groupBy('student_id');
        }

        // Prepare student data for the Admin Gradebook view.
        $students = $students->map(function ($student) use ($records) {
            $studentRecords = $records->get($student->id, collect())
                ->keyBy('term');

            $terms = [];

            foreach ([1, 2, 3] as $term) {
                $record = $studentRecords->get($term);

                $scores = [];

                if ($record) {
                    foreach ($record->assessmentScores as $assessment) {
                        $scores[$assessment->assessment_code] = [
                            'score' => $assessment->score,
                            'highest_possible_score' =>
                                $assessment->highest_possible_score,
                            'group' => $assessment->assessment_group,
                        ];
                    }
                }

                $terms[$term] = [
                    'record_exists' => (bool) $record,
                    'scores' => $scores,

                    // These are saved values from grade_records.
                    'ww_ps' => $record?->ww_ps,
                    'ww_ws' => $record?->ww_ws,
                    'pt_ps' => $record?->pt_ps,
                    'pt_ws' => $record?->pt_ws,
                    'ex_ps' => $record?->ex_ps,
                    'ex_ws' => $record?->ex_ws,
                    'initial_grade' => $record?->initial_grade,
                    'term_grade' => $record?->term_grade,
                    'descriptor' => $record?->descriptor,
                    'status' => $record?->status,
                ];
            }

            return [
                'id' => $student->id,
                'student_id' => $student->student_id,
                'name' => trim(
                    $student->first_name . ' ' .
                    ($student->middle_name
                        ? $student->middle_name . ' '
                        : '') .
                    $student->last_name
                ),
                'section' => $student->section,
                'terms' => $terms,
            ];
        });

        return view('admin.gradebook.index', [
            'subject' => $subject,
            'assignment' => $assignment,
            'teacher' => $assignment?->faculty,
            'advisers' => $advisers,
            'grade' => $grade,
            'gradeLevel' => $gradeLevel,
            'schoolYear' => $schoolYear,
            'students' => $students,
        ]);
    }
}
