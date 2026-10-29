@extends('layouts.teacher')

@section('title', 'Grading Sheet')
@section('header', 'Grading Sheet')
@section('header-description', 'Enter assessment scores and review computed grades for your assigned subject.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/teacher/grade-management.css') }}">
@endsection

@section('content')
    @php
        /*
         * UI prototype only.
         * Replace these sample values with database-backed teacher assignment and learners
         * when the backend workflow is ready.
         */
        $gradebookSubject = $subjectName ?? 'Mathematics';
        $gradebookCode = $subjectCode ?? 'MATH-7';
        $gradebookLevel = $gradeLevel ?? 'Grade 7';
        $gradebookYear = $schoolYear ?? '2026-2027';

        $learners = $students ?? [
            ['id' => 1, 'name' => 'Harvy Hicap'],
            ['id' => 2, 'name' => 'Learner Two'],
            ['id' => 3, 'name' => 'Learner Three'],
            ['id' => 4, 'name' => 'Learner Four'],
            ['id' => 5, 'name' => 'Learner Five'],
            ['id' => 6, 'name' => 'Learner Six'],
            ['id' => 7, 'name' => 'Learner Seven'],
            ['id' => 8, 'name' => 'Learner Eight'],
        ];
    @endphp



    <div class="gradebook-page" data-school-year="{{ $gradebookYear }}" data-subject-code="{{ $gradebookCode }}">


        <div class="grade-management-header">
            <a href="{{ url('/teacher/subjects') }}" class="back-button">
                <i class="fas fa-arrow-left"></i>
                <span>Back to My Subjects</span>
            </a>
        </div>


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
                    <p>{{ $gradebookLevel }} <span class="meta-dot">•</span> Assigned grade level</p>
                </div>
            </div>

            <div class="gradebook-filters">
                <div class="gradebook-filter">
                    <label for="gradebookSchoolYear">School Year</label>
                    <select id="gradebookSchoolYear">
                        <option value="2026-2027" @selected($gradebookYear === '2026-2027')>2026-2027</option>
                        <option value="2027-2028" @selected($gradebookYear === '2027-2028')>2027-2028</option>
                    </select>
                </div>
                <div class="gradebook-filter">
                    <label for="gradebookTerm">Term</label>
                    <select id="gradebookTerm">
                        <option value="1">Term 1</option>
                        <option value="2">Term 2</option>
                        <option value="3">Term 3</option>
                    </select>
                </div>
            </div>

            <div class="policy-note">
                <i class="fa-solid fa-circle-info"></i>
                <div>
                    <strong>Grading computation preview</strong>
                    <p>PPreview of student's grades based on assessment scores for the selected term.</p>
                </div>
            </div>
        </section>

        <section class="gradebook-tabs-card">
            <div class="gradebook-tabs" role="tablist" aria-label="Gradebook views">
                <button class="gradebook-tab is-active" type="button" role="tab" aria-selected="true" data-view="term">Term
                    1</button>
                <button class="gradebook-tab" type="button" role="tab" aria-selected="false" data-view="term">Term
                    2</button>
                <button class="gradebook-tab" type="button" role="tab" aria-selected="false" data-view="term">Term
                    3</button>
                <button class="gradebook-tab" type="button" role="tab" aria-selected="false" data-view="summary">Summary of
                    Grades</button>
            </div>

            <div class="gradebook-toolbar">
                <div>
                    <h3 id="gradebookTableTitle">Term 1 Scores</h3>
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

            <div class="gradebook-table-wrap" id="termGradebook">
                <table class="gradebook-table">
                    <thead>
                        <tr>
                            <th class="col-number" rowspan="2">#</th>
                            <th class="col-learner" rowspan="2">Learner Name</th>
                            <th class="group-heading group-ww" colspan="4">Written / Oral Works <span>(20%)</span></th>
                            <th class="group-heading group-pt" colspan="2">Product / Performance Tasks <span>(50%)</span>
                            </th>
                            <th class="group-heading group-ex" colspan="4">Summative Tests / Term Exam <span>(30%)</span>
                            </th>
                            <th class="computed-heading" rowspan="2">WW<br>PS</th>
                            <th class="computed-heading" rowspan="2">WW<br>WS</th>
                            <th class="computed-heading" rowspan="2">PT<br>PS</th>
                            <th class="computed-heading" rowspan="2">PT<br>WS</th>
                            <th class="computed-heading" rowspan="2">EX<br>PS</th>
                            <th class="computed-heading" rowspan="2">EX<br>WS</th>
                            <th class="computed-heading initial-heading" rowspan="2">Initial<br>Grade</th>
                            <th class="computed-heading" rowspan="2">Term<br>Grade*</th>
                            <th class="computed-heading" rowspan="2">Descriptor*</th>
                        </tr>
                        <tr>
                            <th class="assessment-heading group-ww">WW 1 <small>Max 100</small></th>
                            <th class="assessment-heading group-ww">WW 2 <small>Max 100</small></th>
                            <th class="assessment-heading group-ww">WW 3 <small>Max 100</small></th>
                            <th class="assessment-heading group-ww">WW 4 <small>Max 100</small></th>
                            <th class="assessment-heading group-pt">PT 1 <small>Max 100</small></th>
                            <th class="assessment-heading group-pt">PT 2 <small>Max 100</small></th>
                            <th class="assessment-heading group-ex">ST 1 <small>Max 100</small></th>
                            <th class="assessment-heading group-ex">ST 2 <small>Max 100</small></th>
                            <th class="assessment-heading group-ex">ST 3 <small>Max 100</small></th>
                            <th class="assessment-heading group-ex">Term Exam <small>Max 40</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($learners as $index => $learner)
                            <tr class="learner-row" data-learner-id="{{ $learner['id'] ?? $index + 1 }}">
                                <td class="row-number">{{ $index + 1 }}</td>
                                <td class="learner-name">{{ $learner['name'] ?? 'Learner' }}</td>


                                {{-- TEACHER/ADVISER CAN CHANGE SCORE ITEMS --}}


                                @foreach ([
                                        // Written / Oral Works
                                        ['group' => 'ww', 'max' => 10],
                                        ['group' => 'ww', 'max' => 15],
                                        ['group' => 'ww', 'max' => 15],
                                        ['group' => 'ww', 'max' => 10],

                                        // Performance Tasks
                                        ['group' => 'pt', 'max' => 100],
                                        ['group' => 'pt', 'max' => 100],

                                        // Summative Tests
                                        ['group' => 'ex', 'max' => 20],
                                        ['group' => 'ex', 'max' => 20],
                                        ['group' => 'ex', 'max' => 20],

                                        // Term Exam
                                        ['group' => 'ex', 'max' => 50],
                                    ] as $assessment)

                                    <td class="score-cell">
                                        <input type="number" class="score-input" min="0" max="{{ $assessment['max'] }}" step="any"
                                            inputmode="decimal" data-group="{{ $assessment['group'] }}"
                                            data-max="{{ $assessment['max'] }}"
                                            aria-label="{{ $learner['name'] ?? 'Learner' }} {{ strtoupper($assessment['group']) }} score"
                                            placeholder="—">
                                    </td>
                                @endforeach

                                <td class="computed-cell ww-ps">—</td>
                                <td class="computed-cell ww-ws">—</td>
                                <td class="computed-cell pt-ps">—</td>
                                <td class="computed-cell pt-ws">—</td>
                                <td class="computed-cell ex-ps">—</td>
                                <td class="computed-cell ex-ws">—</td>
                                <td class="computed-cell initial-grade">—</td>
                                <td class="computed-cell term-grade">Pending</td>
                                <td class="computed-cell descriptor"><span class="status-pending">Pending</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

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
                                <tr data-summary-learner-id="{{ $learner['id'] ?? $index + 1 }}">
                                    <td class="summary-number">
                                        {{ $index + 1 }}
                                    </td>

                                    <td class="summary-learner-name">
                                        {{ $learner['name'] ?? 'Learner' }}
                                    </td>

                                    <td class="summary-term-grade" data-term="1">—</td>
                                    <td class="summary-term-grade" data-term="2">—</td>
                                    <td class="summary-term-grade" data-term="3">—</td>

                                    <td class="summary-final-grade">—</td>

                                    <td class="summary-descriptor">
                                        <span class="summary-pending">Pending</span>
                                    </td>

                                    <td class="summary-remark">
                                        <span class="summary-pending">Pending</span>
                                    </td>
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

            <div class="gradebook-footer">
                <div class="computation-legend">
                    <strong><i class="fa-solid fa-circle-info"></i> Computation preview</strong>
                    <p><b>PS</b> = Percentage Score · <b>WS</b> = Weighted Score</p>
                    <p>WW 20% · PT 50% · EX 30%. The Initial Grade is a preview; official Term Grade and Descriptor are
                        pending policy validation.</p>
                </div>
                <div class="gradebook-actions">
                    <button type="button" class="btn btn-light" id="clearScores">
                        <i class="fa-solid fa-eraser"></i> Clear scores
                    </button>
                    <button type="button" class="btn btn-primary" id="saveGradebook">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('page-js')
    <script src="{{ asset('js/teacher/grade-management.js') }}"></script>
@endsection