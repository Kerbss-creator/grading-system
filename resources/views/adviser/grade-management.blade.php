@extends('layouts.adviser')

@section('title', 'Grading Sheet')
@section('header', 'Grading Sheet')
@section('header-description', 'Enter assessment scores and review computed grades for your assigned subject.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/adviser/grade-management.css') }}">
@endsection

@section('content')

    @php
        /*
        |--------------------------------------------------------------------------
        | Gradebook Basic Information
        |--------------------------------------------------------------------------
        */

        $gradebookSubject = $subjectName;
        $gradebookCode = $subjectCode;
        $gradebookLevel = $assignment->grade_level;
        $gradebookYear = $schoolYear->year_name;

        $learners = $students;

        /*
        |--------------------------------------------------------------------------
        | Subject-Based Grading Weights
        |--------------------------------------------------------------------------
        */

        $normalizedSubject = strtoupper(trim($gradebookSubject));

        $isComputerTleMapeh = in_array(
            $normalizedSubject,
            ['COMPUTER', 'COMPUTER EDUCATION', 'TLE', 'MAPEH'],
            true
        );

        $gradingWeights = $isComputerTleMapeh
            ? [
                'ww' => 20,
                'pt' => 60,
                'ex' => 20,
            ]
            : [
                'ww' => 20,
                'pt' => 50,
                'ex' => 30,
            ];

        /*
        |--------------------------------------------------------------------------
        | Assessment Definitions
        |--------------------------------------------------------------------------
        */

        $assessments = [
            ['group' => 'ww', 'label' => 'WW 1', 'max' => 10],
            ['group' => 'ww', 'label' => 'WW 2', 'max' => 15],
            ['group' => 'ww', 'label' => 'WW 3', 'max' => 15],
            ['group' => 'ww', 'label' => 'WW 4', 'max' => 10],

            ['group' => 'pt', 'label' => 'PT 1', 'max' => 100],
            ['group' => 'pt', 'label' => 'PT 2', 'max' => 100],

            ['group' => 'ex', 'label' => 'SA', 'max' => 25, 'weight' => 20],
            ['group' => 'ex', 'label' => 'SB', 'max' => 25, 'weight' => 20],
            ['group' => 'ex', 'label' => 'SC', 'max' => 25, 'weight' => 20],
            ['group' => 'ex', 'label' => 'TE', 'max' => 50, 'weight' => 40],
        ];

        /*
        |--------------------------------------------------------------------------
        | Dynamic Column Counts
        |--------------------------------------------------------------------------
        */

        $assessmentCount = count($assessments);

        $wwCount = count(array_filter(
            $assessments,
            fn($assessment) => $assessment['group'] === 'ww'
        ));

        $ptCount = count(array_filter(
            $assessments,
            fn($assessment) => $assessment['group'] === 'pt'
        ));

        $exCount = count(array_filter(
            $assessments,
            fn($assessment) => $assessment['group'] === 'ex'
        ));

        $assessmentColspan = 2 + 7 + 5 + 10 + 3;

        /*
        |--------------------------------------------------------------------------
        | Student Name Helper
        |--------------------------------------------------------------------------
        */

        $getStudentName = function ($student) {
            return trim(collect([
                $student->first_name ?? null,
                $student->middle_name ?? null,
                $student->last_name ?? null,
            ])->filter()->implode(' ')) ?: 'Unnamed Learner';
        };
    @endphp

    <div class="gradebook-page" data-assignment-id="{{ $assignment->id }}" data-selected-term="{{ $selectedTerm }}"
        data-assessment-settings-url="{{ route('adviser.grade-management.assessment-settings') }}"
        data-save-url="{{ route('adviser.grade-management.save') }}"
        data-load-url="{{ route('adviser.grade-management.load') }}"
        data-reset-url="{{ route('adviser.grade-management.reset') }}"
        data-clear-scores-url="{{ route('adviser.grade-management.clear-scores') }}" data-school-year="{{ $gradebookYear }}"
        data-subject-code="{{ $gradebookCode }}" data-assessments='@json($assessments)'
        data-grading-weights='@json($gradingWeights)'>

        {{-- Gradebook Navigation --}}
        <div class="grade-management-header">
            <a href="{{ route('adviser.subjects', [
        'school_year' => $gradebookYear,
        'term' => $selectedTerm
    ]) }}" class="back-button">
                <i class="fas fa-arrow-left"></i>
                <span>Back to My Subjects</span>
            </a>
        </div>

        {{-- Subject Overview and Filters --}}
        <section class="gradebook-overview">

            <div class="gradebook-subject">
                <div class="gradebook-subject-icon">
                    <i class="fa-solid fa-calculator"></i>
                </div>

                <div>
                    <div class="subject-title-line">
                        <h2>{{ $gradebookSubject }}</h2>
                        <span class="subject-code">{{ $gradebookCode }}</span>
                    </div>

                    <p>
                        {{ $gradebookLevel }}
                        <span class="meta-dot">•</span>
                        Assigned grade level
                    </p>
                </div>
            </div>

            <div class="gradebook-filters">

                <div class="gradebook-filter">
                    <label for="gradebookSchoolYear">School Year</label>

                    <select id="gradebookSchoolYear">
                        @foreach ($schoolYears as $year)
                            <option value="{{ $year->year_name }}" @selected($gradebookYear === $year->year_name)>
                                {{ $year->year_name }}
                                @if ($year->status === 'active')
                                    (Current)
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="gradebook-filter">
                    <label for="gradebookTerm">Term</label>

                    <select id="gradebookTerm">
                        <option value="1" @selected($selectedTerm == 1)>Term 1</option>
                        <option value="2" @selected($selectedTerm == 2)>Term 2</option>
                        <option value="3" @selected($selectedTerm == 3)>Term 3</option>
                    </select>
                </div>

            </div>

            <div class="policy-note">
                <i class="fa-solid fa-circle-info"></i>

                <div>
                    <strong>Grading computation preview</strong>

                    <p>
                        Preview of student grades based on assessment scores
                        for the selected term.
                    </p>
                </div>
            </div>

        </section>

        {{-- Gradebook Tabs, Table, and Summary --}}
        <section class="gradebook-tabs-card">

            {{-- Gradebook View Navigation --}}
            <div class="gradebook-tabs" role="tablist" aria-label="Gradebook views">

                <button class="gradebook-tab {{ $selectedTerm == 1 ? 'is-active' : '' }}" type="button" role="tab"
                    aria-selected="{{ $selectedTerm == 1 ? 'true' : 'false' }}" data-view="term" data-term="1">
                    Term 1
                </button>

                <button class="gradebook-tab {{ $selectedTerm == 2 ? 'is-active' : '' }}" type="button" role="tab"
                    aria-selected="{{ $selectedTerm == 2 ? 'true' : 'false' }}" data-view="term" data-term="2">
                    Term 2
                </button>

                <button class="gradebook-tab {{ $selectedTerm == 3 ? 'is-active' : '' }}" type="button" role="tab"
                    aria-selected="{{ $selectedTerm == 3 ? 'true' : 'false' }}" data-view="term" data-term="3">
                    Term 3
                </button>

                <button class="gradebook-tab" type="button" role="tab" aria-selected="false" data-view="summary">
                    Summary of Grades
                </button>

            </div>

            {{-- Gradebook Toolbar --}}
            <div class="gradebook-toolbar">

                <div>
                    <h3 id="gradebookTableTitle">Term {{ $selectedTerm }} Scores</h3>
                    <p>Enter scores only. Totals and weighted scores update automatically.</p>
                </div>

                <div class="gradebook-toolbar-actions">

                    <button type="button" class="btn btn-light" id="assessmentSettings">
                        <i class="fa-solid fa-sliders"></i>
                        Assessment Settings
                    </button>

                    <button type="button" class="btn btn-light" id="resetGradebook">
                        <i class="fa-solid fa-rotate-left"></i>
                        Reset Gradebook
                    </button>

                </div>
            </div>

            {{-- TERM GRADEBOOK TABLE --}}
            <div class="gradebook-table-wrap" id="termGradebook">

                <table class="gradebook-table">

                    <thead>

                        <tr>

                            <th class="col-number" rowspan="2">#</th>

                            <th class="col-learner" rowspan="2">
                                Learner Name
                            </th>

                            <th class="group-heading group-ww" colspan="{{ $wwCount + 3 }}">
                                Written / Oral Works
                                <span>({{ $gradingWeights['ww'] }}%)</span>
                            </th>

                            <th class="group-heading group-pt" colspan="{{ $ptCount + 3 }}">
                                Product / Performance Tasks
                                <span>({{ $gradingWeights['pt'] }}%)</span>
                            </th>

                            <th class="group-heading group-ex" colspan="{{ $exCount + 6 }}">
                                Summative Tests and Term Examinations
                                <span>({{ $gradingWeights['ex'] }}%)</span>
                            </th>

                            <th class="computed-heading initial-heading" rowspan="2">
                                Initial<br>Grade
                            </th>

                            <th class="computed-heading" rowspan="2">
                                Term<br>Grade
                            </th>

                            <th class="computed-heading" rowspan="2">
                                Descriptor
                            </th>

                        </tr>

                        <tr>

                            {{-- WW Headings --}}
                            @foreach ($assessments as $index => $assessment)
                                @if ($assessment['group'] === 'ww')
                                    <th class="assessment-heading group-ww" data-assessment-index="{{ $index }}">
                                        {{ $assessment['label'] }}
                                        <small>Max {{ $assessment['max'] }}</small>
                                    </th>
                                @endif
                            @endforeach

                            <th class="computed-heading">Total</th>
                            <th class="computed-heading">PS</th>
                            <th class="computed-heading">WS</th>

                            {{-- PT Headings --}}
                            @foreach ($assessments as $index => $assessment)
                                @if ($assessment['group'] === 'pt')
                                    <th class="assessment-heading group-pt" data-assessment-index="{{ $index }}">
                                        {{ $assessment['label'] }}
                                        <small>Max {{ $assessment['max'] }}</small>
                                    </th>
                                @endif
                            @endforeach

                            <th class="computed-heading">Total</th>
                            <th class="computed-heading">PS</th>
                            <th class="computed-heading">WS</th>

                            {{-- EX Headings --}}
                            @foreach ($assessments as $index => $assessment)
                                @if ($assessment['group'] === 'ex')
                                    <th class="assessment-heading group-ex" data-assessment-index="{{ $index }}">
                                        {{ $assessment['label'] }}
                                        <small>Max {{ $assessment['max'] }}</small>
                                    </th>
                                @endif
                            @endforeach

                            <th class="computed-heading">WS SA<br><small>20%</small></th>
                            <th class="computed-heading">WS SB<br><small>20%</small></th>
                            <th class="computed-heading">WS SC<br><small>20%</small></th>
                            <th class="computed-heading">WS TE<br><small>40%</small></th>
                            <th class="computed-heading">Total</th>
                            <th class="computed-heading">WS</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($learners as $index => $learner)

                            @php
                                $learnerName = $getStudentName($learner);
                            @endphp

                            <tr class="learner-row" data-learner-id="{{ $learner->id }}">

                                <td class="row-number">
                                    {{ $index + 1 }}
                                </td>

                                <td class="learner-name">
                                    {{ $learnerName }}
                                </td>

                                {{-- WRITTEN / ORAL WORKS --}}
                                @foreach ($assessments as $assessmentIndex => $assessment)
                                    @if ($assessment['group'] === 'ww')
                                        <td class="score-cell">
                                            <input type="number" class="score-input"
                                                name="scores[{{ $learner->id }}][{{ $assessmentIndex }}]" min="0"
                                                max="{{ $assessment['max'] }}" step="any" inputmode="decimal" data-group="ww"
                                                data-assessment-index="{{ $assessmentIndex }}" data-max="{{ $assessment['max'] }}"
                                                aria-label="{{ $learnerName }} {{ $assessment['label'] }} score" placeholder="—">
                                        </td>
                                    @endif
                                @endforeach

                                <td class="computed-cell ww-total">—</td>
                                <td class="computed-cell ww-ps">—</td>
                                <td class="computed-cell ww-ws">—</td>

                                {{-- PRODUCT / PERFORMANCE TASKS --}}
                                @foreach ($assessments as $assessmentIndex => $assessment)
                                    @if ($assessment['group'] === 'pt')
                                        <td class="score-cell">
                                            <input type="number" class="score-input"
                                                name="scores[{{ $learner->id }}][{{ $assessmentIndex }}]" min="0"
                                                max="{{ $assessment['max'] }}" step="any" inputmode="decimal" data-group="pt"
                                                data-assessment-index="{{ $assessmentIndex }}" data-max="{{ $assessment['max'] }}"
                                                aria-label="{{ $learnerName }} {{ $assessment['label'] }} score" placeholder="—">
                                        </td>
                                    @endif
                                @endforeach

                                <td class="computed-cell pt-total">—</td>
                                <td class="computed-cell pt-ps">—</td>
                                <td class="computed-cell pt-ws">—</td>

                                {{-- SUMMATIVE TESTS / TERM EXAM --}}
                                @foreach ($assessments as $assessmentIndex => $assessment)
                                    @if ($assessment['group'] === 'ex')
                                        <td class="score-cell">
                                            <input type="number" class="score-input"
                                                name="scores[{{ $learner->id }}][{{ $assessmentIndex }}]" min="0"
                                                max="{{ $assessment['max'] }}" step="any" inputmode="decimal" data-group="ex"
                                                data-assessment-index="{{ $assessmentIndex }}" data-max="{{ $assessment['max'] }}"
                                                aria-label="{{ $learnerName }} {{ $assessment['label'] }} score" placeholder="—">
                                        </td>
                                    @endif
                                @endforeach

                                {{-- Individual Exam Weighted Scores --}}
                                <td class="computed-cell sa-ws">—</td>
                                <td class="computed-cell sb-ws">—</td>
                                <td class="computed-cell sc-ws">—</td>
                                <td class="computed-cell te-ws">—</td>

                                <td class="computed-cell ex-total">—</td>
                                <td class="computed-cell ex-ws">—</td>

                                {{-- FINAL GRADE COLUMNS --}}
                                <td class="computed-cell initial-grade">—</td>

                                <td class="computed-cell term-grade">
                                    Pending
                                </td>

                                <td class="computed-cell descriptor">
                                    <span class="status-pending">Pending</span>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="{{ $assessmentColspan }}" class="summary-no-learners">
                                    No learners available for this subject.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{-- SUMMARY OF GRADES --}}
            <div class="summary-view is-hidden" id="summaryGradebook">

                <div class="summary-school-header">

                    <img src="{{ asset('images/forbesologo.png') }}" alt="Forbes Academy Logo" class="summary-school-logo">

                    <div class="summary-school-title">

                        <h3>
                            SUMMARY OF GRADES IN
                            {{ strtoupper($gradebookSubject) }}
                            {{ strtoupper($gradebookLevel) }}
                        </h3>

                        <p>School Year {{ $gradebookYear }}</p>

                    </div>

                </div>

                <div class="summary-table-wrap">

                    <table class="summary-grades-table">

                        <thead>
                            <tr>
                                <th class="summary-number-column">#</th>
                                <th class="summary-learner-column">LEARNERS</th>
                                <th>TERM 1</th>
                                <th>TERM 2</th>
                                <th>TERM 3</th>
                                <th class="summary-final-column">FINAL GRADE</th>
                                <th class="summary-result-column">DESCRIPTOR</th>
                                <th class="summary-result-column">REMARK</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse ($learners as $index => $learner)

                                <tr class="summary-learner-row" data-learner-id="{{ $learner->id }}">

                                    <td class="summary-number">
                                        {{ $index + 1 }}
                                    </td>

                                    <td class="summary-learner-name">
                                        {{ $getStudentName($learner) }}
                                    </td>

                                    <td class="summary-pending">Pending</td>
                                    <td class="summary-pending">Pending</td>
                                    <td class="summary-pending">Pending</td>
                                    <td class="summary-final-grade">Pending</td>
                                    <td class="summary-pending">Pending</td>
                                    <td class="summary-pending">Pending</td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="8" class="summary-no-learners">
                                        No learners available for this subject.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="summary-footer-note">
                    <i class="fa-solid fa-circle-info"></i>

                    <span>
                        Term grades, final grades, descriptors, and remarks will appear
                        when the approved grading computation and saved results are available.
                    </span>
                </div>

            </div>

            {{-- Gradebook Footer and Actions --}}
            <div class="gradebook-footer">

                <div class="computation-legend">

                    <strong>
                        <i class="fa-solid fa-circle-info"></i>
                        Computation preview
                    </strong>

                    <p>
                        <b>PS</b> = Percentage Score ·
                        <b>WS</b> = Weighted Score
                    </p>

                    <p>
                        WW {{ $gradingWeights['ww'] }}% ·
                        PT {{ $gradingWeights['pt'] }}% ·
                        EX {{ $gradingWeights['ex'] }}%.

                        The Initial Grade is a preview; official Term Grade
                        and Descriptor are pending policy validation.
                    </p>

                </div>

                <div class="gradebook-actions">

                    <button type="button" class="btn btn-light" id="clearScores">
                        <i class="fa-solid fa-eraser"></i>
                        Clear scores
                    </button>

                    <button type="button" class="btn btn-primary" id="saveGradebook">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save Changes
                    </button>

                </div>

            </div>

        </section>

        {{-- ASSESSMENT SETTINGS MODAL --}}
        <div class="assessment-modal-overlay is-hidden" id="assessmentSettingsModal" aria-hidden="true">

            <div class="assessment-modal" role="dialog" aria-modal="true" aria-labelledby="assessmentSettingsTitle">

                <div class="assessment-modal-header">

                    <div>
                        <h3 id="assessmentSettingsTitle">Assessment Settings</h3>
                        <p>Manage the maximum points for each assessment.</p>
                    </div>

                    <button type="button" class="assessment-modal-close" id="closeAssessmentSettings"
                        aria-label="Close assessment settings">
                        <i class="fa-solid fa-xmark"></i>
                    </button>

                </div>

                <div class="assessment-modal-body">

                    <div class="assessment-weight-info">

                        <h4>Grading Component Weights</h4>

                        <div class="assessment-weight-list">

                            <div>
                                <span>Written / Oral Works</span>
                                <strong>{{ $gradingWeights['ww'] }}%</strong>
                            </div>

                            <div>
                                <span>Product / Performance Tasks</span>
                                <strong>{{ $gradingWeights['pt'] }}%</strong>
                            </div>

                            <div>
                                <span>Summative Tests / Term Exam</span>
                                <strong>{{ $gradingWeights['ex'] }}%</strong>
                            </div>

                        </div>

                        <p>
                            Component weights are fixed and cannot be edited here.
                        </p>

                    </div>

                    <div class="assessment-settings-fields">

                        <h4>Assessment Maximum Points</h4>

                        <div id="assessmentSettingsFields">
                            {{-- JavaScript generates the assessment fields here. --}}
                        </div>

                    </div>

                    <p class="assessment-settings-note">
                        <i class="fa-solid fa-circle-info"></i>
                        Changes will apply only to the currently selected term.
                    </p>

                </div>

                <div class="assessment-modal-footer">

                    <button type="button" class="btn btn-light" id="cancelAssessmentSettings">
                        Cancel
                    </button>

                    <button type="button" class="btn btn-primary" id="saveAssessmentSettings">
                        <i class="fa-solid fa-check"></i>
                        Apply Changes
                    </button>

                </div>

            </div>

        </div>

        {{-- CLEAR SCORES WARNING MODAL --}}
        <div class="warning-modal-overlay is-hidden" id="clearScoresModal">

            <div class="warning-modal" role="dialog" aria-modal="true" aria-labelledby="clearScoresTitle">

                <div class="warning-modal-icon">!</div>

                <h3 id="clearScoresTitle">Clear Scores?</h3>

                <p>
                    All scores for Term
                    <strong id="clearScoresTerm">{{ $selectedTerm }}</strong>
                    will be cleared. This action cannot be undone.
                </p>

                <div class="warning-modal-actions">

                    <button type="button" class="warning-cancel-btn" data-close-warning>
                        Cancel
                    </button>

                    <button type="button" class="warning-confirm-btn" id="confirmClearScores">
                        Clear Scores
                    </button>

                </div>

            </div>

        </div>

        {{-- RESET GRADEBOOK WARNING MODAL --}}
        <div class="warning-modal-overlay is-hidden" id="resetGradebookModal">

            <div class="warning-modal" role="dialog" aria-modal="true" aria-labelledby="resetGradebookTitle">

                <div class="warning-modal-icon">!</div>

                <h3 id="resetGradebookTitle">Reset Gradebook?</h3>

                <p>
                    The assessment maximum points for Term
                    <strong id="resetGradebookTerm">{{ $selectedTerm }}</strong>
                    will return to their default values.
                    Existing student scores will remain unchanged.
                </p>

                <div class="warning-modal-actions">

                    <button type="button" class="warning-cancel-btn" data-close-warning>
                        Cancel
                    </button>

                    <button type="button" class="warning-confirm-btn" id="confirmResetGradebook">
                        Reset Gradebook
                    </button>

                </div>

            </div>

        </div>

    </div>

    {{-- GRADE LOCK MODAL --}}
    <div class="grade-lock-overlay" id="gradeLockModal">

        <div class="grade-lock-modal">

            <div class="grade-lock-icon">
                <i class="fa-solid fa-lock"></i>
            </div>

            <h2>Grades Locked</h2>

            <p>
                These grades have already been submitted
                and can no longer be edited.
            </p>

            <div class="grade-lock-notice">
                <i class="fa-solid fa-circle-info"></i>
                Please contact your Adviser or Administrator
                if these grades need corrections.
            </div>

            <button type="button" id="closeGradeLockModal">
                Okay, Got It
            </button>

        </div>

    </div>

@endsection

@section('page-js')
    <script src="{{ asset('js/adviser/grade-management.js') }}"></script>
@endsection