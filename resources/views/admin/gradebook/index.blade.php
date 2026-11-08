@extends('layouts.admin')

@section('title', 'Admin Gradebook')
@section('header', 'Admin Gradebook')
@section('header-description', 'View student assessment scores and academic records.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/gradebook.css?v=4') }}">
@endsection

@section('content')

    <div class="admin-gradebook">

        {{-- BACK BUTTON --}}
        <div class="gradebook-management-header">
            <a href="{{ url()->previous() }}" class="gradebook-back-button">
                <i class="fa-solid fa-arrow-left"></i>
                Back
            </a>
        </div>

        {{-- SUBJECT INFORMATION --}}
        <div class="gradebook-info-card">

            <div class="gradebook-subject">
                <div class="gradebook-subject-icon">
                    <i class="fa-solid fa-book-open"></i>
                </div>

                <div>
                    <span class="info-label">SUBJECT</span>

                    <div class="subject-title-line">
                        <h2>{{ $subject->subject_name }}</h2>
                        <span class="subject-code">
                            {{ $subject->subject_code }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="gradebook-info-details">

                <div class="gradebook-info-item">
                    <span class="info-label">GRADE LEVEL</span>
                    <strong>{{ $gradeLevel }}</strong>
                </div>

                <div class="gradebook-info-item">
                    <span class="info-label">SCHOOL YEAR</span>
                    <strong>{{ $schoolYear }}</strong>
                </div>

                <div class="gradebook-info-item">
                    <span class="info-label">SUBJECT TEACHER</span>
                    <strong>
                        @if($teacher)
                            {{ trim($teacher->first_name . ' ' . ($teacher->middle_name ? $teacher->middle_name . ' ' : '') . $teacher->last_name) }}
                        @else
                            Not Assigned
                        @endif
                    </strong>
                </div>

                <div class="gradebook-info-item">
                    <span class="info-label">ADVISER</span>
                    <strong>
                        @forelse($advisers as $adviser)
                            {{ trim($adviser->first_name . ' ' . ($adviser->middle_name ? $adviser->middle_name . ' ' : '') . $adviser->last_name) }}@if(!$loop->last),
                            @endif
                        @empty
                            Not Assigned
                        @endforelse
                    </strong>
                </div>

            </div>

            <span class="readonly-label">
                <i class="fas fa-eye"></i>
                Read Only
            </span>

        </div>

        {{-- GRADEBOOK TABS AND PANELS --}}
        <div class="gradebook-tabs-card">

            {{-- TABS --}}
            <div class="gradebook-tabs">

                <button type="button" class="gradebook-tab is-active" data-tab="term-1">
                    Term 1
                </button>

                <button type="button" class="gradebook-tab" data-tab="term-2">
                    Term 2
                </button>

                <button type="button" class="gradebook-tab" data-tab="term-3">
                    Term 3
                </button>

                <button type="button" class="gradebook-tab" data-tab="summary">
                    Summary of Grades
                </button>

            </div>

            {{-- TERM TABLES --}}
            @foreach([1, 2, 3] as $term)

                <div class="gradebook-panel {{ $term === 1 ? '' : 'is-hidden' }}" id="term-{{ $term }}">

                    <div class="gradebook-panel-heading">
                        <div>
                            <h3>Term {{ $term }} Grade Records</h3>
                            <p>Saved assessment scores and computed grades.</p>
                        </div>
                    </div>

                    {{-- TERM TABLE --}}
                    <div class="gradebook-table-wrapper">

                        <table class="admin-gradebook-table">

                            <thead>
                                <tr>
                                    <th rowspan="2">Student ID</th>
                                    <th rowspan="2">Student Name</th>

                                    <th colspan="4" class="group-ww">
                                        Written Works
                                    </th>

                                    <th colspan="2" class="group-pt">
                                        Performance Tasks
                                    </th>

                                    <th colspan="4" class="group-ex">
                                        Examinations
                                    </th>

                                    <th colspan="2" class="group-ww">
                                        WW
                                    </th>

                                    <th colspan="2" class="group-pt">
                                        PT
                                    </th>

                                    <th colspan="2" class="group-ex">
                                        EX
                                    </th>

                                    <th rowspan="2" class="initial-heading">
                                        Initial Grade
                                    </th>

                                    <th rowspan="2">
                                        Term Grade
                                    </th>

                                    <th rowspan="2">
                                        Descriptor
                                    </th>

                                    <th rowspan="2">
                                        Status
                                    </th>
                                </tr>

                                <tr>
                                    {{-- WRITTEN WORKS --}}
                                    <th class="group-ww assessment-heading">WW1</th>
                                    <th class="group-ww assessment-heading">WW2</th>
                                    <th class="group-ww assessment-heading">WW3</th>
                                    <th class="group-ww assessment-heading">WW4</th>

                                    {{-- PERFORMANCE TASKS --}}
                                    <th class="group-pt assessment-heading">PT1</th>
                                    <th class="group-pt assessment-heading">PT2</th>

                                    {{-- EXAMINATIONS --}}
                                    <th class="group-ex assessment-heading">SA</th>
                                    <th class="group-ex assessment-heading">SB</th>
                                    <th class="group-ex assessment-heading">SC</th>
                                    <th class="group-ex assessment-heading">TE</th>

                                    {{-- COMPUTED COMPONENTS --}}
                                    <th class="computed-heading">PS</th>
                                    <th class="computed-heading">WS</th>

                                    <th class="computed-heading">PS</th>
                                    <th class="computed-heading">WS</th>

                                    <th class="computed-heading">PS</th>
                                    <th class="computed-heading">WS</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($students as $student)

                                    @php
                                        $record = $student['terms'][$term];
                                        $scores = $record['scores'];
                                    @endphp

                                    <tr>

                                        {{-- STUDENT INFORMATION --}}
                                        <td>{{ $student['student_id'] }}</td>

                                        <td>
                                            {{ $student['name'] }}
                                        </td>

                                        {{-- WRITTEN WORKS --}}
                                        @foreach(['WW1', 'WW2', 'WW3', 'WW4'] as $code)

                                            <td>
                                                @if(isset($scores[$code]))
                                                    {{ $scores[$code]['score'] ?? '—' }}
                                                    <small>
                                                        / {{ $scores[$code]['highest_possible_score'] }}
                                                    </small>
                                                @else
                                                    —
                                                @endif
                                            </td>

                                        @endforeach

                                        {{-- PERFORMANCE TASKS --}}
                                        @foreach(['PT1', 'PT2'] as $code)

                                            <td>
                                                @if(isset($scores[$code]))
                                                    {{ $scores[$code]['score'] ?? '—' }}
                                                    <small>
                                                        / {{ $scores[$code]['highest_possible_score'] }}
                                                    </small>
                                                @else
                                                    —
                                                @endif
                                            </td>

                                        @endforeach

                                        {{-- EXAMINATIONS --}}
                                        @foreach(['SA', 'SB', 'SC', 'TE'] as $code)

                                            <td>
                                                @if(isset($scores[$code]))
                                                    {{ $scores[$code]['score'] ?? '—' }}
                                                    <small>
                                                        / {{ $scores[$code]['highest_possible_score'] }}
                                                    </small>
                                                @else
                                                    —
                                                @endif
                                            </td>

                                        @endforeach

                                        {{-- SAVED PS / WS --}}
                                        <td class="computed-cell ww-ps">
                                            {{ $record['ww_ps'] ?? '—' }}
                                        </td>

                                        <td class="computed-cell ww-ws">
                                            {{ $record['ww_ws'] ?? '—' }}
                                        </td>

                                        <td class="computed-cell pt-ps">
                                            {{ $record['pt_ps'] ?? '—' }}
                                        </td>

                                        <td class="computed-cell pt-ws">
                                            {{ $record['pt_ws'] ?? '—' }}
                                        </td>

                                        <td class="computed-cell ex-ps">
                                            {{ $record['ex_ps'] ?? '—' }}
                                        </td>

                                        <td class="computed-cell ex-ws">
                                            {{ $record['ex_ws'] ?? '—' }}
                                        </td>

                                        {{-- SAVED GRADES --}}
                                        <td class="initial-grade">
                                            {{ $record['initial_grade'] ?? '—' }}
                                        </td>

                                        <td class="computed-cell term-grade">
                                            {{ $record['term_grade'] ?? '—' }}
                                        </td>

                                        <td>
                                            {{ $record['descriptor'] ?? '—' }}
                                        </td>

                                        {{-- STATUS --}}
                                        <td>
                                            @if(!$record['record_exists'])

                                                <span class="grade-status pending">
                                                    No Record
                                                </span>

                                            @else

                                                <span class="grade-status {{ str_replace('_', '-', $record['status'] ?? 'draft') }}">
                                                    {{ ucwords(str_replace('_', ' ', $record['status'] ?? 'draft')) }}
                                                </span>

                                            @endif
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="22" class="empty-gradebook">
                                            No active students found for this grade level and school year.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            @endforeach

            {{-- SUMMARY OF GRADES --}}
            <div class="gradebook-panel is-hidden" id="summary">

                <div class="gradebook-panel-heading">
                    <div>
                        <h3>Summary of Grades</h3>
                        <p>Overview of the saved grades for all three terms.</p>
                    </div>

                    <span class="readonly-label">
                        <i class="fas fa-eye"></i>
                        Read Only
                    </span>
                </div>

                <div class="gradebook-table-wrapper">

                    <table class="summary-table">

                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Term 1</th>
                                <th>Term 2</th>
                                <th>Term 3</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($students as $student)

                                <tr>
                                    <td>
                                        {{ $student['student_id'] }}
                                    </td>

                                    <td>
                                        {{ $student['name'] }}
                                    </td>


                                    @foreach([1, 2, 3] as $term)

                                        <td class="summary-final-grade">
                                            {{ $student['terms'][$term]['term_grade'] ?? '—' }}
                                        </td>

                                    @endforeach

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="6" class="summary-no-learners">
                                        No student records available.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="summary-footer-note">
                    <i class="fas fa-circle-info"></i>

                    <span>
                        This page displays saved academic records for reference.
                        Only authorized users can manage or update grade submissions.
                    </span>
                </div>

            </div>

        </div>

    </div>

    {{-- GRADEBOOK TAB SCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const tabs = document.querySelectorAll('.gradebook-tab');
            const panels = document.querySelectorAll('.gradebook-panel');

            tabs.forEach(tab => {

                tab.addEventListener('click', function () {

                    const target = this.dataset.tab;

                    // Remove active state from all tabs
                    tabs.forEach(item => {
                        item.classList.remove('is-active');
                    });

                    // Hide all panels
                    panels.forEach(panel => {
                        panel.classList.add('is-hidden');
                    });

                    // Activate selected tab
                    this.classList.add('is-active');

                    // Show selected panel
                    const selectedPanel = document.getElementById(target);

                    if (selectedPanel) {
                        selectedPanel.classList.remove('is-hidden');
                    }

                });

            });

        });
    </script>

@endsection