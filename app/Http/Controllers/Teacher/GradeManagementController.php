<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\GradeRecord;
use App\Models\GradeAssessmentScore;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GradeManagementController extends Controller
{
    /**
     * Default assessment definitions.
     */
    private function assessments(): array
    {
        return [
            ['code' => 'WW1', 'group' => 'WW', 'max' => 10],
            ['code' => 'WW2', 'group' => 'WW', 'max' => 15],
            ['code' => 'WW3', 'group' => 'WW', 'max' => 15],
            ['code' => 'WW4', 'group' => 'WW', 'max' => 10],
            ['code' => 'PT1', 'group' => 'PT', 'max' => 100],
            ['code' => 'PT2', 'group' => 'PT', 'max' => 100],
            ['code' => 'SA', 'group' => 'EX', 'max' => 25],
            ['code' => 'SB', 'group' => 'EX', 'max' => 25],
            ['code' => 'SC', 'group' => 'EX', 'max' => 25],
            ['code' => 'TE', 'group' => 'EX', 'max' => 50],
        ];
    }

    /**
     * Calculate and save the student's computed grades.
     */
    private function updateComputedGrades(
        GradeRecord $record,
        string $subjectName
    ): void {
        $scores = $record->assessmentScores()
            ->get()
            ->keyBy('assessment_code');

        $groups = [
            'WW' => ['WW1', 'WW2', 'WW3', 'WW4'],
            'PT' => ['PT1', 'PT2'],
            'EX' => ['SA', 'SB', 'SC', 'TE'],
        ];

        // TLE, MAPEH, and Computer use performance-subject weights.
        $isPerformanceSubject = in_array(
            strtolower(trim($subjectName)),
            ['tle', 'mapeh', 'computer', 'computer education'],
            true
        );

        $weights = $isPerformanceSubject
            ? ['WW' => 20, 'PT' => 60, 'EX' => 20]
            : ['WW' => 20, 'PT' => 50, 'EX' => 30];

        $computed = [
            'ww_ps' => null,
            'ww_ws' => null,
            'pt_ps' => null,
            'pt_ws' => null,
            'ex_ps' => null,
            'ex_ws' => null,
            'initial_grade' => null,
            'term_grade' => null,
            'descriptor' => null,
        ];

        $groupComplete = [];

        foreach ($groups as $group => $codes) {
            $earned = 0;
            $possible = 0;
            $complete = true;

            foreach ($codes as $code) {
                $assessment = $scores->get($code);

                if (
                    !$assessment ||
                    $assessment->score === null ||
                    $assessment->highest_possible_score <= 0
                ) {
                    $complete = false;
                    break;
                }

                $earned += (float) $assessment->score;
                $possible += (float) $assessment->highest_possible_score;
            }

            $groupComplete[$group] = $complete;

            if (!$complete || $possible <= 0) {
                continue;
            }

            $percentage = ($earned / $possible) * 100;

            if ($group === 'WW') {
                $computed['ww_ps'] = round($percentage, 2);

                $computed['ww_ws'] = round(
                    $percentage * ($weights['WW'] / 100),
                    2
                );
            }

            if ($group === 'PT') {
                $computed['pt_ps'] = round($percentage, 2);

                $computed['pt_ws'] = round(
                    $percentage * ($weights['PT'] / 100),
                    2
                );
            }

            if ($group === 'EX') {
                $computed['ex_ps'] = round($percentage, 2);

                // Individual examination component weights.
                $examWeights = [
                    'SA' => 20,
                    'SB' => 20,
                    'SC' => 20,
                    'TE' => 40,
                ];

                $examPercentage = 0;

                foreach ($examWeights as $code => $weight) {
                    $assessment = $scores->get($code);

                    $componentPercentage =
                        (
                            (float) $assessment->score /
                            (float) $assessment->highest_possible_score
                        ) * 100;

                    $examPercentage +=
                        $componentPercentage * ($weight / 100);
                }

                $computed['ex_ws'] = round(
                    $examPercentage * ($weights['EX'] / 100),
                    2
                );
            }
        }

        // Initial Grade is calculated only when all assessments are complete.
        if (
            $groupComplete['WW'] &&
            $groupComplete['PT'] &&
            $groupComplete['EX']
        ) {
            $computed['initial_grade'] = round(
                $computed['ww_ws'] +
                $computed['pt_ws'] +
                $computed['ex_ws'],
                2
            );
        }

        // Apply the DepEd transmutation table.
        if ($computed['initial_grade'] !== null) {
            $termGrade = $this->transmuteGrade(
                (float) $computed['initial_grade']
            );

            if ($termGrade !== null) {
                $computed['term_grade'] = $termGrade;

                $computed['descriptor'] = $this->getGradeDescriptor(
                    $termGrade
                );
            }
        }

        $record->update($computed);
    }

    /**
     * Convert Initial Grade into the corresponding Term Grade.
     */
    private function transmuteGrade(float $initialGrade): ?int
    {
        if (!is_finite($initialGrade)) {
            return null;
        }

        $ranges = [
            [99.5, 100.0, 100],
            [98.32, 99.49, 99],
            [97.14, 98.31, 98],
            [95.96, 97.13, 97],
            [94.78, 95.95, 96],
            [93.6, 94.77, 95],
            [92.42, 93.59, 94],
            [91.24, 92.41, 93],
            [90.06, 91.23, 92],
            [88.88, 90.05, 91],
            [87.7, 88.87, 90],
            [86.52, 87.69, 89],
            [85.34, 86.51, 88],
            [84.16, 85.33, 87],
            [82.98, 84.15, 86],
            [81.8, 82.97, 85],
            [80.62, 81.79, 84],
            [79.44, 80.61, 83],
            [78.26, 79.43, 82],
            [77.08, 78.25, 81],
            [75.9, 77.07, 80],
            [74.72, 75.89, 79],
            [73.54, 74.71, 78],
            [72.36, 73.53, 77],
            [71.18, 72.35, 76],
            [70.0, 71.17, 75],
            [65.34, 69.99, 74],
            [60.67, 65.33, 73],
            [56.01, 60.66, 72],
            [51.34, 56.0, 71],
            [46.67, 51.33, 70],
            [42.01, 46.66, 69],
            [37.34, 42.0, 68],
            [32.68, 37.33, 67],
            [28.01, 32.67, 66],
            [23.35, 28.0, 65],
            [18.68, 23.34, 64],
            [14.01, 18.67, 63],
            [9.35, 14.0, 62],
            [4.68, 9.34, 61],
            [0.0, 4.67, 60],
        ];

        foreach ($ranges as [$minimum, $maximum, $grade]) {
            if (
                $initialGrade >= $minimum &&
                $initialGrade <= $maximum
            ) {
                return $grade;
            }
        }

        return null;
    }

    /**
     * Get the descriptor based on the transmuted Term Grade.
     */
    private function getGradeDescriptor(int $termGrade): string
    {
        if ($termGrade >= 90) {
            return 'Advancing';
        }

        if ($termGrade >= 80) {
            return 'Benchmarking';
        }

        if ($termGrade >= 75) {
            return 'Connecting';
        }

        if ($termGrade >= 65) {
            return 'Developing';
        }

        return 'Emerging';
    }

    /**
     * Get the authenticated faculty member.
     */
    private function getFaculty(Request $request): Faculty
    {
        $faculty = Faculty::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->first();

        abort_unless($faculty, 404);

        return $faculty;
    }

    /**
     * Get the teaching assignment belonging to the authenticated faculty.
     */
    private function getAssignment(Request $request, Faculty $faculty)
    {
        return $faculty->teachingAssignments()
            ->where(
                'id',
                $request->input(
                    'assignment',
                    $request->query('assignment')
                )
            )
            ->firstOrFail();
    }

    /**
     * Display the teacher grade management page.
     */
    public function index(Request $request)
    {
        $faculty = $this->getFaculty($request);

        // Get the active school year.
        $activeSchoolYear = SchoolYear::active();

        abort_unless(
            $activeSchoolYear,
            403,
            'No active school year is configured.'
        );

        // Use the active school year as the default.
        $schoolYearName = $request->query(
            'school_year',
            $activeSchoolYear->year_name
        );

        // Find the selected school year.
        $schoolYearRecord = SchoolYear::where(
            'year_name',
            $schoolYearName
        )->firstOrFail();

        // Get all school years for the dropdown.
        $schoolYears = SchoolYear::orderByDesc('year_name')->get();

        // Get the original assignment selected by the teacher.
        $selectedAssignment = $faculty->teachingAssignments()
            ->where('id', $request->query('assignment'))
            ->firstOrFail();

        // Find the matching assignment for the selected school year.
        $assignment = $faculty->teachingAssignments()
            ->where('subject', $selectedAssignment->subject)
            ->where('grade_level', $selectedAssignment->grade_level)
            ->where('school_year', $schoolYearRecord->year_name)
            ->firstOrFail();

        // Use the selected school's current term as the default.
        $term = (int) $request->query(
            'term',
            $schoolYearRecord->current_term
        );

        abort_unless(in_array($term, [1, 2, 3], true), 422);

        // Get active students assigned to this grade level and school year.
        $students = Student::where(
            'grade_level',
            $assignment->grade_level
        )
            ->where('school_year', $schoolYearRecord->year_name)
            ->where('status', 'active')
            ->orderBy('last_name')
            ->get();

        $gradeRecords = GradeRecord::with('assessmentScores')
            ->where('teaching_assignment_id', $assignment->id)
            ->where('school_year', $schoolYearRecord->year_name)
            ->where('term', $term)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $students = $students->map(function ($student) use ($gradeRecords) {
            $record = $gradeRecords->get($student->id);

            $scores = [];

            if ($record) {
                foreach ($record->assessmentScores as $score) {
                    $scores[$score->assessment_code] = $score->score;
                }
            }

            return [
                'id' => $student->id,
                'name' => trim(
                    $student->first_name . ' ' .
                    ($student->middle_name ? $student->middle_name . ' ' : '') .
                    $student->last_name
                ),
                'scores' => $scores,
            ];
        });

        $subjectCodes = [
            'Mathematics' => 'MATH',
            'Science' => 'SCI',
            'English' => 'ENG',
            'Araling Panlipunan' => 'AP',
            'TLE' => 'TLE',
            'MAPEH' => 'MAPEH',
            'Filipino' => 'FIL',
            'Values Education' => 'VE',
            'Computer Education' => 'COMP',
        ];

        $subjectName = $assignment->subject;

        $prefix = $subjectCodes[$subjectName]
            ?? strtoupper(substr($subjectName, 0, 3));

        $gradeNumber = preg_replace('/\D/', '', $assignment->grade_level);

        $subjectCode = $prefix . '-' . $gradeNumber;

        return view('teacher.grade-management', [
            'assignment' => $assignment,
            'subjectName' => $subjectName,
            'subjectCode' => $subjectCode,
            'gradeLevel' => $assignment->grade_level,
            'schoolYear' => $schoolYearRecord->year_name,
            'schoolYears' => $schoolYears,
            'students' => $students,
            'selectedTerm' => $term,
        ]);
    }

    /**
     * Load saved assessment scores and maximum points for the selected term.
     */
    public function load(Request $request)
    {
        $faculty = $this->getFaculty($request);
        $assignment = $this->getAssignment($request, $faculty);

        $validated = $request->validate([
            'assignment' => ['required', 'integer'],
            'school_year' => ['required', 'string', 'max:20'],
            'term' => ['required', 'integer', 'in:1,2,3'],
        ]);

        $schoolYear = $validated['school_year'];
        $term = (int) $validated['term'];

        $studentIds = Student::where('grade_level', $assignment->grade_level)
            ->where('school_year', $schoolYear)
            ->where('status', 'active')
            ->pluck('id');

        $gradeRecords = GradeRecord::with('assessmentScores')
            ->where('teaching_assignment_id', $assignment->id)
            ->where('school_year', $schoolYear)
            ->where('term', $term)
            ->whereIn('student_id', $studentIds)
            ->get();

        $definitions = $this->assessments();

        $assessmentIndexes = [];

        foreach ($definitions as $index => $definition) {
            $assessmentIndexes[$definition['code']] = $index;
        }

        $scores = [];
        $maxScores = [];

        foreach ($gradeRecords as $record) {
            $studentId = (string) $record->student_id;

            foreach ($record->assessmentScores as $assessment) {
                $code = $assessment->assessment_code;

                if (!isset($assessmentIndexes[$code])) {
                    continue;
                }

                $index = $assessmentIndexes[$code];

                $scores[$studentId][$index] = $assessment->score;

                // Keep the first saved maximum for each assessment.
                if (!isset($maxScores[$index])) {
                    $maxScores[$index] = $assessment->highest_possible_score;
                }
            }
        }

        foreach ($definitions as $index => $definition) {
            $maxScores[$index] ??= $definition['max'];
        }

        return response()->json([
            'success' => true,
            'term' => $term,
            'scores' => $scores,
            'max_scores' => $maxScores,
        ]);
    }

    /**
     * Save assessment maximum points for the selected term.
     */
    public function updateAssessmentSettings(Request $request)
    {
        $faculty = $this->getFaculty($request);
        $assignment = $this->getAssignment($request, $faculty);

        $definitions = $this->assessments();

        $rules = [
            'assignment' => ['required', 'integer'],
            'school_year' => ['required', 'string', 'max:20'],
            'term' => ['required', 'integer', 'in:1,2,3'],
            'max_scores' => ['required', 'array'],
        ];

        foreach ($definitions as $index => $definition) {
            $rules["max_scores.$index"] = [
                'required',
                'numeric',
                'gt:0',
            ];
        }

        $validated = $request->validate($rules);

        $schoolYear = $validated['school_year'];
        $term = (int) $validated['term'];
        $maxScores = $validated['max_scores'];

        $students = Student::where('grade_level', $assignment->grade_level)
            ->where('school_year', $schoolYear)
            ->where('status', 'active')
            ->get();

        DB::transaction(function () use ($students, $assignment, $schoolYear, $term, $definitions, $maxScores) {
            $studentIds = $students->pluck('id');

            $records = GradeRecord::where(
                'teaching_assignment_id',
                $assignment->id
            )
                ->where('school_year', $schoolYear)
                ->where('term', $term)
                ->whereIn('student_id', $studentIds)
                ->get()
                ->keyBy('student_id');

            // Protect submitted or approved records.
            foreach ($records as $record) {
                if (!in_array($record->status, ['draft', 'returned'], true)) {
                    throw ValidationException::withMessages([
                        'max_scores' =>
                            'Assessment settings cannot be changed because some grades are already submitted or reviewed.',
                    ]);
                }
            }

            $recordIds = $records->pluck('id');

            $existingScores = GradeAssessmentScore::whereIn(
                'grade_record_id',
                $recordIds
            )->get();

            // Prevent a new maximum from being lower than an existing score.
            foreach ($existingScores as $existingScore) {
                $index = collect($definitions)->search(
                    fn($definition) =>
                        $definition['code'] === $existingScore->assessment_code
                );

                if ($index === false) {
                    continue;
                }

                $newMaximum = (float) $maxScores[$index];

                if (
                    $existingScore->score !== null &&
                    (float) $existingScore->score > $newMaximum
                ) {
                    throw ValidationException::withMessages([
                        'max_scores' =>
                            "The new maximum for {$existingScore->assessment_code} is lower than an existing student score.",
                    ]);
                }
            }

            // Create records when needed and persist all assessment settings.
            foreach ($students as $student) {
                $record = $records->get($student->id);

                if (!$record) {
                    $record = new GradeRecord([
                        'student_id' => $student->id,
                        'teaching_assignment_id' => $assignment->id,
                        'school_year' => $schoolYear,
                        'term' => $term,
                        'status' => 'draft',
                    ]);

                    $record->save();
                }

                foreach ($definitions as $index => $definition) {
                    GradeAssessmentScore::updateOrCreate(
                        [
                            'grade_record_id' => $record->id,
                            'assessment_code' => $definition['code'],
                        ],
                        [
                            'assessment_group' => $definition['group'],
                            'highest_possible_score' => $maxScores[$index],
                        ]
                    );
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Assessment maximum points saved successfully.',
        ]);
    }

    /**
     * Reset assessment maximum points to their default values.
     * Existing student scores will remain unchanged.
     */
    public function reset(Request $request)
    {
        $faculty = $this->getFaculty($request);
        $assignment = $this->getAssignment($request, $faculty);

        $validated = $request->validate([
            'assignment' => ['required', 'integer'],
            'school_year' => ['required', 'string', 'max:20'],
            'term' => ['required', 'integer', 'in:1,2,3'],
        ]);

        $schoolYear = $validated['school_year'];
        $term = (int) $validated['term'];

        $definitions = $this->assessments();

        $students = Student::where('grade_level', $assignment->grade_level)
            ->where('school_year', $schoolYear)
            ->where('status', 'active')
            ->get();

        DB::transaction(function () use ($students, $assignment, $schoolYear, $term, $definitions) {
            $studentIds = $students->pluck('id');

            $records = GradeRecord::where(
                'teaching_assignment_id',
                $assignment->id
            )
                ->where('school_year', $schoolYear)
                ->where('term', $term)
                ->whereIn('student_id', $studentIds)
                ->get();

            foreach ($records as $record) {
                if (!in_array($record->status, ['draft', 'returned'], true)) {
                    throw ValidationException::withMessages([
                        'term' => 'Cannot reset assessment settings because some grades are already submitted or reviewed.',
                    ]);
                }
            }

            foreach ($records as $record) {
                foreach ($definitions as $definition) {
                    GradeAssessmentScore::updateOrCreate(
                        [
                            'grade_record_id' => $record->id,
                            'assessment_code' => $definition['code'],
                        ],
                        [
                            'assessment_group' => $definition['group'],
                            'highest_possible_score' => $definition['max'],
                        ]
                    );
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Assessment maximum points have been restored to their default values. Existing student scores were retained.',
            'max_scores' => collect($definitions)
                ->mapWithKeys(fn($item, $index) => [$index => $item['max']]),
        ]);
    }

    /**
     * Clear all student scores for the selected term.
     * Assessment maximum points will remain unchanged.
     */
    public function clearScores(Request $request)
    {
        $faculty = $this->getFaculty($request);
        $assignment = $this->getAssignment($request, $faculty);

        $validated = $request->validate([
            'assignment' => ['required', 'integer'],
            'school_year' => ['required', 'string', 'max:20'],
            'term' => ['required', 'integer', 'in:1,2,3'],
        ]);

        $schoolYear = $validated['school_year'];
        $term = (int) $validated['term'];

        $students = Student::where('grade_level', $assignment->grade_level)
            ->where('school_year', $schoolYear)
            ->where('status', 'active')
            ->pluck('id');

        DB::transaction(function () use ($students, $assignment, $schoolYear, $term) {
            $records = GradeRecord::where(
                'teaching_assignment_id',
                $assignment->id
            )
                ->where('school_year', $schoolYear)
                ->where('term', $term)
                ->whereIn('student_id', $students)
                ->get();

            foreach ($records as $record) {
                if (!in_array($record->status, ['draft', 'returned'], true)) {
                    throw ValidationException::withMessages([
                        'term' => 'Cannot clear scores because some grades are already submitted or reviewed.',
                    ]);
                }
            }

            GradeAssessmentScore::whereIn(
                'grade_record_id',
                $records->pluck('id')
            )->update([
                        'score' => null,
                    ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'All student scores for the selected term have been cleared.',
        ]);
    }

    /**
     * Save assessment scores as draft.
     */
    public function save(Request $request)
    {
        $faculty = $this->getFaculty($request);
        $assignment = $this->getAssignment($request, $faculty);

        $validated = $request->validate([
            'assignment' => ['required', 'integer'],
            'school_year' => ['required', 'string', 'max:20'],
            'term' => ['required', 'integer', 'in:1,2,3'],
            'scores' => ['required', 'array'],
            'scores.*' => ['array'],
            'scores.*.*' => ['nullable', 'numeric', 'min:0'],
            'max_scores' => ['required', 'array'],
            'max_scores.*' => ['required', 'numeric', 'gt:0'],
        ]);

        $definitions = $this->assessments();

        $schoolYear = $validated['school_year'];
        $term = (int) $validated['term'];

        $allowedStudents = Student::where(
            'grade_level',
            $assignment->grade_level
        )
            ->where('school_year', $schoolYear)
            ->where('status', 'active')
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->all();

        DB::transaction(function () use ($validated, $definitions, $allowedStudents, $assignment, $schoolYear, $term) {
            foreach ($validated['scores'] as $studentId => $studentScores) {
                if (!in_array((string) $studentId, $allowedStudents, true)) {
                    throw ValidationException::withMessages([
                        'scores' => 'Invalid student in grade submission.',
                    ]);
                }

                $record = GradeRecord::firstOrNew([
                    'student_id' => $studentId,
                    'teaching_assignment_id' => $assignment->id,
                    'school_year' => $schoolYear,
                    'term' => $term,
                ]);

                if (!$record->exists) {
                    $record->status = 'draft';
                }

                if (
                    $record->exists &&
                    !in_array($record->status, ['draft', 'returned'], true)
                ) {
                    throw ValidationException::withMessages([
                        'scores' => 'Only draft or returned grades can be edited.',
                    ]);
                }

                $record->save();

                foreach ($definitions as $index => $definition) {
                    $scoreValue = $studentScores[$index] ?? null;
                    $maximum = $validated['max_scores'][$index] ?? null;

                    if ($maximum === null || $maximum <= 0) {
                        throw ValidationException::withMessages([
                            'max_scores' =>
                                "Invalid maximum score for {$definition['code']}.",
                        ]);
                    }

                    if ($scoreValue !== null && $scoreValue !== '') {
                        if ($scoreValue < 0 || $scoreValue > $maximum) {
                            throw ValidationException::withMessages([
                                'scores' =>
                                    "Invalid score for {$definition['code']}.",
                            ]);
                        }
                    } else {
                        $scoreValue = null;
                    }

                    GradeAssessmentScore::updateOrCreate(
                        [
                            'grade_record_id' => $record->id,
                            'assessment_code' => $definition['code'],
                        ],
                        [
                            'assessment_group' => $definition['group'],
                            'score' => $scoreValue,
                            'highest_possible_score' => $maximum,
                        ]
                    );
                }

                // Automatically calculate and save computed grades.
                $this->updateComputedGrades(
                    $record,
                    $assignment->subject
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Grade scores and computed grades saved successfully.',
        ]);
    }
}
