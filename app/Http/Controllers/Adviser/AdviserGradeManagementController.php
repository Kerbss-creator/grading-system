<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\Student;
use App\Models\SchoolYear;
use App\Models\TeachingAssignment;
use App\Models\GradeRecord;
use App\Models\GradeAssessmentScore;
use App\Models\AdvisoryAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdviserGradeManagementController extends Controller
{
    /**
     * Assessment definitions used by the grading sheet.
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
     * Get the active Faculty record of the authenticated Adviser.
     */
    private function getFaculty(Request $request): Faculty
    {
        return Faculty::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->firstOrFail();
    }

    /**
     * Resolve the selected school year.
     */
    private function getSchoolYear(Request $request): SchoolYear
    {
        $activeSchoolYear = SchoolYear::active();

        abort_unless(
            $activeSchoolYear,
            403,
            'No active school year is configured.'
        );

        $selectedYear = $request->input(
            'school_year',
            $activeSchoolYear->year_name
        );

        return SchoolYear::where('year_name', $selectedYear)
            ->firstOrFail();
    }

    /**
     * Resolve only a teaching assignment owned by this Adviser
     * and belonging to the selected school year.
     */
    private function getAssignment(
        Request $request,
        Faculty $faculty,
        SchoolYear $schoolYear
    ): TeachingAssignment {
        $assignmentId = $request->input('assignment');

        abort_unless($assignmentId, 404, 'Teaching assignment not found.');

        return $faculty->teachingAssignments()
            ->whereKey($assignmentId)
            ->where('school_year', $schoolYear->year_name)
            ->firstOrFail();
    }

    /**
     * Display the Adviser grading sheet.
     */
    public function index(Request $request)
    {
        $faculty = $this->getFaculty($request);

        $schoolYear = $this->getSchoolYear($request);

        $assignment = $this->getAssignment(
            $request,
            $faculty,
            $schoolYear
        );

        // Validate the selected term without silently converting invalid input.
        $validated = $request->validate([
            'term' => ['sometimes', 'integer', 'in:1,2,3'],
        ]);

        $selectedTerm = (int) (
            $validated['term'] ?? $schoolYear->current_term
        );

        // Get active students assigned to the same grade level.
        $students = Student::where(
            'grade_level',
            $assignment->grade_level
        )
            ->where('school_year', $schoolYear->year_name)
            ->where('status', 'active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Get existing grade records for this assignment and term.
        $gradeRecords = GradeRecord::with('assessmentScores')
            ->where('teaching_assignment_id', $assignment->id)
            ->where('school_year', $schoolYear->year_name)
            ->where('term', $selectedTerm)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        // Attach grade records to their corresponding students.
        $students->each(function ($student) use ($gradeRecords) {
            $student->gradeRecord = $gradeRecords->get($student->id);
        });

        $schoolYears = SchoolYear::orderByDesc('year_name')->get();

        // Prepare subject information.
        $subjectName = $assignment->subject;

        $subjectCodes = [
            'Mathematics' => 'MATH',
            'Science' => 'SCI',
            'English' => 'ENG',
            'Araling Panlipunan' => 'AP',
            'TLE' => 'TLE',
            'MAPEH' => 'MAPEH',
            'Filipino' => 'FIL',
            'Values Education' => 'VE',
            'Computer' => 'COMP',
            'Computer Education' => 'COMP',
        ];

        $normalizedSubject = strtolower(trim($subjectName));

        $subjectCode = $subjectCodes[$subjectName]
            ?? strtoupper(substr(
                preg_replace('/[^A-Za-z]/', '', $normalizedSubject),
                0,
                4
            ));

        $assessments = $this->assessments();

        // Grading weights.
        $performanceSubjects = [
            'tle',
            'mapeh',
            'computer',
            'computer education',
        ];

        $gradingWeights = in_array(
            $normalizedSubject,
            $performanceSubjects,
            true
        )
            ? ['WW' => 20, 'PT' => 60, 'EX' => 20]
            : ['WW' => 20, 'PT' => 50, 'EX' => 30];

        $selectedSchoolYear = $schoolYear->year_name;

        return view('adviser.grade-management', compact(
            'faculty',
            'assignment',
            'students',
            'gradeRecords',
            'schoolYears',
            'schoolYear',
            'selectedSchoolYear',
            'selectedTerm',
            'subjectName',
            'subjectCode',
            'assessments',
            'gradingWeights'
        ));
    }

    /**
     * Load saved assessment scores and maximum points.
     */
    public function load(Request $request)
    {
        $validated = $request->validate([
            'assignment' => ['required', 'integer'],
            'school_year' => ['required', 'string', 'max:20'],
            'term' => ['required', 'integer', 'in:1,2,3'],
        ]);

        $faculty = $this->getFaculty($request);

        $schoolYear = SchoolYear::where(
            'year_name',
            $validated['school_year']
        )->firstOrFail();

        $assignment = $this->getAssignment(
            $request,
            $faculty,
            $schoolYear
        );

        $term = (int) $validated['term'];

        $studentIds = Student::where(
            'grade_level',
            $assignment->grade_level
        )
            ->where('school_year', $schoolYear->year_name)
            ->where('status', 'active')
            ->pluck('id');

        $gradeRecords = GradeRecord::with('assessmentScores')
            ->where('teaching_assignment_id', $assignment->id)
            ->where('school_year', $schoolYear->year_name)
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

                if (!isset($maxScores[$index])) {
                    $maxScores[$index] =
                        $assessment->highest_possible_score;
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
     * Save assessment scores as draft.
     */
    public function save(Request $request)
    {
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

        $allowedIndexes = array_map(
            'strval',
            array_keys($definitions)
        );

        // Reject unknown assessment indexes.
        foreach ($validated['scores'] as $studentScores) {
            $extraIndexes = array_diff(
                array_map('strval', array_keys($studentScores)),
                $allowedIndexes
            );

            if (!empty($extraIndexes)) {
                throw ValidationException::withMessages([
                    'scores' => 'Invalid assessment index submitted.',
                ]);
            }
        }

        // Ensure all expected maximum scores are provided.
        foreach (array_keys($definitions) as $index) {
            if (!array_key_exists($index, $validated['max_scores'])) {
                throw ValidationException::withMessages([
                    'max_scores' => 'Missing maximum score for an assessment.',
                ]);
            }
        }

        // Reject unknown maximum score indexes.
        $extraMaxIndexes = array_diff(
            array_map(
                'strval',
                array_keys($validated['max_scores'])
            ),
            $allowedIndexes
        );

        if (!empty($extraMaxIndexes)) {
            throw ValidationException::withMessages([
                'max_scores' => 'Invalid assessment maximum score index.',
            ]);
        }

        $faculty = $this->getFaculty($request);

        $schoolYearRecord = SchoolYear::where(
            'year_name',
            $validated['school_year']
        )->firstOrFail();

        $assignment = $this->getAssignment(
            $request,
            $faculty,
            $schoolYearRecord
        );

        $schoolYear = $schoolYearRecord->year_name;
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

                if (
                    !in_array(
                        (string) $studentId,
                        $allowedStudents,
                        true
                    )
                ) {
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

                if (
                    $record->exists &&
                    !in_array(
                        $record->status,
                        ['draft', 'returned'],
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        'scores' => 'Only draft or returned grades can be edited.',
                    ]);
                }

                if (!$record->exists) {
                    $record->status = 'draft';
                }

                $record->save();

                foreach ($definitions as $index => $definition) {
                    $scoreValue = $studentScores[$index] ?? null;
                    $maximum = $validated['max_scores'][$index];

                    if ($maximum <= 0) {
                        throw ValidationException::withMessages([
                            'max_scores' =>
                                "Invalid maximum score for {$definition['code']}.",
                        ]);
                    }

                    if ($scoreValue !== null && $scoreValue !== '') {
                        if ($scoreValue > $maximum) {
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

    /**
     * Automatically compute WW, PT, EX, Initial Grade,
     * Term Grade, and Descriptor.
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
                        ((float) $assessment->score /
                            (float) $assessment->highest_possible_score) * 100;

                    $examPercentage +=
                        $componentPercentage * ($weight / 100);
                }

                $computed['ex_ws'] = round(
                    $examPercentage * ($weights['EX'] / 100),
                    2
                );
            }
        }

        // Compute Initial Grade only when all assessment groups are complete.
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

        // Convert Initial Grade to DepEd Term Grade using the transmutation table.
        if ($computed['initial_grade'] !== null) {
            $termGrade = $this->transmuteGrade(
                (float) $computed['initial_grade']
            );

            if ($termGrade !== null) {
                $computed['term_grade'] = $termGrade;
                $computed['descriptor'] = $this->getGradeDescriptor($termGrade);
            }
        }

        $record->update($computed);
    }

    /**
     * Convert Initial Grade to Term Grade using the same
     * transmutation ranges used by the Teacher grading sheet.
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
     * Save assessment maximum points for the selected term.
     */
    public function updateAssessmentSettings(Request $request)
    {
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

        $faculty = $this->getFaculty($request);

        $schoolYearRecord = SchoolYear::where(
            'year_name',
            $validated['school_year']
        )->firstOrFail();

        $assignment = $this->getAssignment(
            $request,
            $faculty,
            $schoolYearRecord
        );

        $schoolYear = $schoolYearRecord->year_name;
        $term = (int) $validated['term'];
        $maxScores = $validated['max_scores'];

        $students = Student::where(
            'grade_level',
            $assignment->grade_level
        )
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

            foreach ($records as $record) {
                if (
                    !in_array(
                        $record->status,
                        ['draft', 'returned'],
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        'max_scores' =>
                            'Assessment settings cannot be changed because some grades are already submitted or reviewed.',
                    ]);
                }
            }

            $existingScores = GradeAssessmentScore::whereIn(
                'grade_record_id',
                $records->pluck('id')
            )->get();

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

                // Recalculate grades using the updated maximum points.
                $this->updateComputedGrades(
                    $record,
                    $assignment->subject
                );
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
        $validated = $request->validate([
            'assignment' => ['required', 'integer'],
            'school_year' => ['required', 'string', 'max:20'],
            'term' => ['required', 'integer', 'in:1,2,3'],
        ]);

        $faculty = $this->getFaculty($request);

        $schoolYearRecord = SchoolYear::where(
            'year_name',
            $validated['school_year']
        )->firstOrFail();

        $assignment = $this->getAssignment(
            $request,
            $faculty,
            $schoolYearRecord
        );

        $schoolYear = $schoolYearRecord->year_name;
        $term = (int) $validated['term'];

        $definitions = $this->assessments();

        $students = Student::where(
            'grade_level',
            $assignment->grade_level
        )
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
                if (
                    !in_array(
                        $record->status,
                        ['draft', 'returned'],
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        'term' =>
                            'Cannot reset assessment settings because some grades are already submitted or reviewed.',
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

                // Recalculate grades using the restored default maximum points.
                $this->updateComputedGrades(
                    $record,
                    $assignment->subject
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Assessment maximum points have been restored to their default values. Existing student scores were retained.',
            'max_scores' => collect($definitions)
                ->mapWithKeys(
                    fn($item, $index) => [$index => $item['max']]
                ),
        ]);
    }

    /**
     * Clear all student assessment scores for the selected term.
     * Maximum points and grade records will remain unchanged.
     */
    public function clearScores(Request $request)
    {
        $validated = $request->validate([
            'assignment' => ['required', 'integer'],
            'school_year' => ['required', 'string', 'max:20'],
            'term' => ['required', 'integer', 'in:1,2,3'],
        ]);

        $faculty = $this->getFaculty($request);

        $schoolYearRecord = SchoolYear::where(
            'year_name',
            $validated['school_year']
        )->firstOrFail();

        $assignment = $this->getAssignment(
            $request,
            $faculty,
            $schoolYearRecord
        );

        $schoolYear = $schoolYearRecord->year_name;
        $term = (int) $validated['term'];

        $studentIds = Student::where(
            'grade_level',
            $assignment->grade_level
        )
            ->where('school_year', $schoolYear)
            ->where('status', 'active')
            ->pluck('id');

        DB::transaction(function () use ($studentIds, $assignment, $schoolYear, $term) {
            $records = GradeRecord::where(
                'teaching_assignment_id',
                $assignment->id
            )
                ->where('school_year', $schoolYear)
                ->where('term', $term)
                ->whereIn('student_id', $studentIds)
                ->get();

            foreach ($records as $record) {
                if (
                    !in_array(
                        $record->status,
                        ['draft', 'returned'],
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        'term' =>
                            'Cannot clear scores because some grades are already submitted or reviewed.',
                    ]);
                }
            }

            // Clear scores only. Preserve maximum points and grade records.
            GradeAssessmentScore::whereIn(
                'grade_record_id',
                $records->pluck('id')
            )->update([
                        'score' => null,
                    ]);

            // Clear previously computed grade values.
            foreach ($records as $record) {
                $record->update([
                    'ww_ps' => null,
                    'ww_ws' => null,
                    'pt_ps' => null,
                    'pt_ws' => null,
                    'ex_ps' => null,
                    'ex_ws' => null,
                    'initial_grade' => null,
                    'term_grade' => null,
                    'descriptor' => null,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'All student scores for the selected term have been cleared.',
        ]);
    }


    /**
     * Submit the selected term's grades for Adviser review
     * or directly to Administrator when this Adviser owns
     * the advisory assignment for the same grade level.
     */
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'assignment' => ['required', 'integer'],
            'school_year' => ['required', 'string', 'max:20'],
            'term' => ['required', 'integer', 'in:1,2,3'],
        ]);

        $faculty = $this->getFaculty($request);

        $schoolYearRecord = SchoolYear::where(
            'year_name',
            $validated['school_year']
        )->firstOrFail();

        $assignment = $this->getAssignment(
            $request,
            $faculty,
            $schoolYearRecord
        );

        $schoolYear = $schoolYearRecord->year_name;
        $term = (int) $validated['term'];

        // Find the Adviser assigned to this grade level and school year.
        // A school-year-specific assignment takes priority over a
        // general assignment whose school_year is null.
        $adviserAssignments = AdvisoryAssignment::where(
            'grade_level',
            $assignment->grade_level
        )
            ->where('school_year', $schoolYear)
            ->get();

        if ($adviserAssignments->count() > 1) {
            throw ValidationException::withMessages([
                'assignment' =>
                    'Multiple Advisers are assigned to this grade level for the selected school year. Please contact the Administrator.',
            ]);
        }

        $assignedAdviser = $adviserAssignments->first();

        if (!$assignedAdviser) {
            $generalAssignments = AdvisoryAssignment::where(
                'grade_level',
                $assignment->grade_level
            )
                ->whereNull('school_year')
                ->get();

            if ($generalAssignments->count() > 1) {
                throw ValidationException::withMessages([
                    'assignment' =>
                        'Multiple Advisers are assigned to this grade level. Please contact the Administrator.',
                ]);
            }

            $assignedAdviser = $generalAssignments->first();
        }

        if (!$assignedAdviser) {
            throw ValidationException::withMessages([
                'assignment' =>
                    'No Adviser is assigned to this grade level for the selected school year. Grades cannot be submitted yet.',
            ]);
        }

        // Determine the submission destination automatically.
        $isOwnAdvisoryClass =
            (int) $assignedAdviser->faculty_id === (int) $faculty->id;

        $nextStatus = $isOwnAdvisoryClass
            ? 'forwarded_to_admin'
            : 'submitted';

        // Get all active students under this teaching assignment's grade level.
        $students = Student::where(
            'grade_level',
            $assignment->grade_level
        )
            ->where('school_year', $schoolYear)
            ->where('status', 'active')
            ->get(['id']);

        if ($students->isEmpty()) {
            throw ValidationException::withMessages([
                'assignment' =>
                    'There are no active students for this grade level and school year.',
            ]);
        }

        $studentIds = $students->pluck('id');

        $definitions = $this->assessments();

        DB::transaction(function () use ($assignment, $schoolYear, $term, $studentIds, $definitions, $nextStatus) {
            // Lock the batch records while validating and submitting.
            $records = GradeRecord::with('assessmentScores')
                ->where('teaching_assignment_id', $assignment->id)
                ->where('school_year', $schoolYear)
                ->where('term', $term)
                ->whereIn('student_id', $studentIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('student_id');

            // Every active student must have a grade record.
            foreach ($studentIds as $studentId) {
                if (!$records->has($studentId)) {
                    throw ValidationException::withMessages([
                        'grades' =>
                            'Some students do not have grade records yet. Please save the gradebook before submitting.',
                    ]);
                }
            }

            // Validate the entire batch before changing any status.
            foreach ($records as $record) {
                if (!in_array($record->status, ['draft', 'returned'], true)) {
                    throw ValidationException::withMessages([
                        'grades' =>
                            'Some grades have already been submitted or reviewed. Only draft or returned grades can be submitted.',
                    ]);
                }

                $savedAssessments = $record->assessmentScores
                    ->keyBy('assessment_code');

                foreach ($definitions as $definition) {
                    $assessment = $savedAssessments->get(
                        $definition['code']
                    );

                    if (
                        !$assessment ||
                        $assessment->score === null ||
                        (float) $assessment->highest_possible_score <= 0
                    ) {
                        throw ValidationException::withMessages([
                            'grades' =>
                                "Incomplete grades detected. Please complete {$definition['code']} for every student before submitting.",
                        ]);
                    }

                    if (
                        (float) $assessment->score < 0 ||
                        (float) $assessment->score >
                        (float) $assessment->highest_possible_score
                    ) {
                        throw ValidationException::withMessages([
                            'grades' =>
                                "An invalid score was found for {$definition['code']}. Please review the gradebook.",
                        ]);
                    }
                }
            }

            // All records passed validation. Submit the whole batch.
            foreach ($records as $record) {
                $record->update([
                    'status' => $nextStatus,
                    'submitted_at' => now(),

                    // Clear previous review information when resubmitting.
                    'adviser_reviewed_at' => null,
                    'admin_reviewed_at' => null,
                    'admin_return_reason' => null,
                ]);
            }
        });

        $message = $isOwnAdvisoryClass
            ? 'Grades successfully submitted directly to the Administrator for approval.'
            : 'Grades successfully submitted to the Adviser for review.';

        return response()->json([
            'success' => true,
            'status' => $nextStatus,
            'message' => $message,
        ]);
    }

}
