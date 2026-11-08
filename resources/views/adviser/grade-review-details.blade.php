@extends('layouts.adviser')

@section('title', 'Grade Submission Details')
@section('header', 'Grade Submission Details')
@section('header-description', 'Review student assessment scores and submitted grades.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/adviser/review-details.css') }}">
@endsection

@section('content')

    @php
        $termLabels = [
            1 => '1st Term',
            2 => '2nd Term',
            3 => '3rd Term',
        ];

        $termLabel = $termLabels[$term] ?? 'Term';

        $statusLabels = [
            'submitted' => 'Pending Adviser Review',
            'forwarded_to_admin' => 'Forwarded to Admin',
            'approved' => 'Approved',
            'returned' => 'Returned to Teacher',
        ];

        $assessmentCodes = [
            'WW' => ['WW1', 'WW2', 'WW3', 'WW4'],
            'PT' => ['PT1', 'PT2'],
            'EX' => ['SA', 'SB', 'SC', 'TE'],
        ];
    @endphp

    <div class="grade-review-details-page">

        {{-- BACK BUTTON --}}
        <div class="review-back-wrapper">
            <a href="{{ route('adviser.grade-review') }}" class="review-back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Grade Review
            </a>
        </div>

        {{-- SUBMISSION INFORMATION --}}
        <div class="submission-details-card">

            <div class="submission-details-header">
                <div>
                    <span class="submission-label">GRADE SUBMISSION</span>

                    <h2>{{ $assignment->subject }}</h2>

                    <p>
                        <i class="fas fa-user-tie"></i>
                        Teacher:
                        {{ $assignment->faculty?->first_name }}
                        {{ $assignment->faculty?->middle_name }}
                        {{ $assignment->faculty?->last_name }}
                    </p>
                </div>

                <span class="submission-status status-{{ $batchStatus }}">
                    {{ $statusLabels[$batchStatus] ?? ucfirst(str_replace('_', ' ', $batchStatus)) }}
                </span>
            </div>

            <div class="submission-info-grid">

                <div class="submission-info-item">
                    <span>Grade Level</span>
                    <strong>{{ $assignment->grade_level }}</strong>
                </div>

                <div class="submission-info-item">
                    <span>School Year</span>
                    <strong>{{ $schoolYear }}</strong>
                </div>

                <div class="submission-info-item">
                    <span>Academic Term</span>
                    <strong>{{ $termLabel }}</strong>
                </div>

                <div class="submission-info-item">
                    <span>Total Students</span>
                    <strong>{{ $gradeRecords->count() }}</strong>
                </div>

            </div>
        </div>

        {{-- STUDENT GRADE TABLE --}}
        <div class="student-grades-card">

            <div class="student-grades-header">
                <div>
                    <h3>Student Assessment Records</h3>
                    <p>Review the individual assessment scores and computed grades.</p>
                </div>

                <span class="student-count-badge">
                    {{ $gradeRecords->count() }} Students
                </span>
            </div>

            <div class="assessment-legend">
                <span><i class="legend-dot ww-dot"></i> Written Works</span>
                <span><i class="legend-dot pt-dot"></i> Performance Tasks</span>
                <span><i class="legend-dot ex-dot"></i> Examination</span>
            </div>

            <div class="grade-table-wrapper">

                <table class="student-assessment-table">

                    <thead>

                        <tr class="assessment-group-row">
                            <th rowspan="2" class="student-column">
                                Student Information
                            </th>

                            <th colspan="4" class="ww-heading">
                                Written Works
                            </th>

                            <th colspan="2" class="pt-heading">
                                Performance Tasks
                            </th>

                            <th colspan="4" class="ex-heading">
                                Examination
                            </th>

                            <th rowspan="2">Initial Grade</th>
                            <th rowspan="2">Term Grade</th>
                            <th rowspan="2">Descriptor</th>
                        </tr>

                        <tr class="assessment-code-row">

                            @foreach($assessmentCodes['WW'] as $code)
                                <th>{{ $code }}</th>
                            @endforeach

                            @foreach($assessmentCodes['PT'] as $code)
                                <th>{{ $code }}</th>
                            @endforeach

                            @foreach($assessmentCodes['EX'] as $code)
                                <th>{{ $code }}</th>
                            @endforeach

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($gradeRecords as $record)

                                        @php
                                            $scores = $record->assessmentScores->keyBy('assessment_code');

                                            $student = $record->student;
                                        @endphp

                                        <tr>

                                            {{-- STUDENT --}}
                                            <td class="student-column">

                                                <div class="student-name">
                                                    {{ $student?->last_name }},
                                                    {{ $student?->first_name }}
                                                    {{ $student?->middle_name }}
                                                </div>

                                                <div class="student-id">
                                                    ID: {{ $student?->student_id ?? 'N/A' }}
                                                </div>

                                            </td>

                                            {{-- WRITTEN WORKS --}}
                                            @foreach($assessmentCodes['WW'] as $code)

                                                @php
                                                    $score = $scores->get($code);
                                                @endphp

                                                <td>
                                                    @if($score && $score->score !== null)
                                                        <span class="score-value">
                                                            {{ number_format((float) $score->score, 2) }}
                                                            <small>/ {{ number_format((float) $score->highest_possible_score, 2) }}</small>
                                                        </span>
                                                    @else
                                                        <span class="missing-score">—</span>
                                                    @endif
                                                </td>

                                            @endforeach

                                            {{-- PERFORMANCE TASKS --}}
                                            @foreach($assessmentCodes['PT'] as $code)

                                                @php
                                                    $score = $scores->get($code);
                                                @endphp

                                                <td>
                                                    @if($score && $score->score !== null)
                                                        <span class="score-value">
                                                            {{ number_format((float) $score->score, 2) }}
                                                            <small>/ {{ number_format((float) $score->highest_possible_score, 2) }}</small>
                                                        </span>
                                                    @else
                                                        <span class="missing-score">—</span>
                                                    @endif
                                                </td>

                                            @endforeach

                                            {{-- EXAMINATION --}}
                                            @foreach($assessmentCodes['EX'] as $code)

                                                @php
                                                    $score = $scores->get($code);
                                                @endphp

                                                <td>
                                                    @if($score && $score->score !== null)
                                                        <span class="score-value">
                                                            {{ number_format((float) $score->score, 2) }}
                                                            <small>/ {{ number_format((float) $score->highest_possible_score, 2) }}</small>
                                                        </span>
                                                    @else
                                                        <span class="missing-score">—</span>
                                                    @endif
                                                </td>

                                            @endforeach

                                            {{-- COMPUTED GRADES --}}
                                            <td class="computed-grade">
                                                {{ $record->initial_grade !== null
                            ? number_format((float) $record->initial_grade, 2)
                            : '—' }}
                                            </td>

                                            <td class="computed-grade term-grade">
                                                {{ $record->term_grade !== null
                            ? number_format((float) $record->term_grade, 2)
                            : '—' }}
                                            </td>

                                            <td>
                                                {{ $record->descriptor ?? '—' }}
                                            </td>

                                        </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div class="grade-table-note">
                <i class="fas fa-info-circle"></i>
                Assessment scores are displayed using the saved records submitted by the Teacher.
            </div>

        </div>

        {{-- ADVISER ACTIONS --}}
        @if($batchStatus === 'submitted')

            <div class="adviser-review-actions">

                <div class="review-action-description">
                    <h3>Review Decision</h3>
                    <p>
                        Once reviewed, you may forward the entire submission
                        to the Administrator or return it to the Teacher.
                    </p>
                </div>

                <div class="review-action-buttons">

                    {{-- RETURN TO TEACHER --}}
                    <form method="POST" action="{{ route('adviser.grade-review.return', [
                'assignmentId' => $assignment->id,
                'schoolYear' => $schoolYear,
                'term' => $term
            ]) }}"
                        onsubmit="return confirm('Are you sure you want to return the entire grade submission to the Teacher?');">
                        @csrf

                        <button type="submit" class="return-grade-btn">
                            <i class="fas fa-rotate-left"></i>
                            Return to Teacher
                        </button>
                    </form>

                    {{-- FORWARD TO ADMIN --}}
                    <form method="POST" action="{{ route('adviser.grade-review.forward', [
                'assignmentId' => $assignment->id,
                'schoolYear' => $schoolYear,
                'term' => $term
            ]) }}"
                        onsubmit="return confirm('Are you sure you want to forward the entire grade submission to the Administrator?');">
                        @csrf

                        <button type="submit" class="forward-grade-btn">
                            <i class="fas fa-paper-plane"></i>
                            Forward to Admin
                        </button>
                    </form>

                </div>

            </div>

        @else

            <div class="review-completed-notice">
                <i class="fas fa-circle-check"></i>

                <div>
                    <strong>Adviser Review Completed</strong>
                    <p>
                        This submission is no longer awaiting an Adviser decision.
                    </p>
                </div>
            </div>

        @endif

    </div>

@endsection