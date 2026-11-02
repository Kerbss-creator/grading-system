{{-- ADVISER ACADEMIC RECORDS --}}
@extends('layouts.adviser')

@section('title', 'Academic Records')
@section('header', 'Academic Records')
@section('header-description', 'View academic records for students in your assigned section.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/adviser/adviser-academic-records.css') }}">
@endsection

@section('content')
<div class="academic-records-page" id="academicRecordsPage">
    {{-- SUBJECT OVERVIEW --}}
    <section class="ar-view" id="subjectOverview">
        <div class="ar-page-intro">
            <div>
                <p class="ar-eyebrow">ACADEMIC MANAGEMENT</p>
                <h2>Subjects</h2>
                <p class="ar-muted">Choose a subject to view the students and their grades by term.</p>
            </div>
            <div class="ar-section-chip">
                <i class="fa-solid fa-users" aria-hidden="true"></i>
                <span>Assigned Section: <strong>{{ $assignedSectionName ?? 'Grade 7-A' }}</strong></span>
            </div>
        </div>

        <div class="ar-subject-grid" id="arSubjectGrid">
            {{-- Sample UI data. Replace with database-driven subjects when the controller is ready. --}}
            @php
                $academicRecordSubjects = $academicRecordSubjects ?? [
                    ['name' => 'Mathematics', 'icon' => 'fa-calculator', 'tone' => 'green'],
                    ['name' => 'English', 'icon' => 'fa-book-open', 'tone' => 'blue'],
                    ['name' => 'Science', 'icon' => 'fa-flask', 'tone' => 'orange'],
                    ['name' => 'Filipino', 'icon' => 'fa-book', 'tone' => 'purple'],
                    ['name' => 'Araling Panlipunan', 'icon' => 'fa-earth-asia', 'tone' => 'teal'],
                    ['name' => 'MAPEH', 'icon' => 'fa-music', 'tone' => 'rose'],
                    ['name' => 'Technology and Livelihood Education', 'icon' => 'fa-screwdriver-wrench', 'tone' => 'slate'],
                    ['name' => 'Values Education', 'icon' => 'fa-heart', 'tone' => 'gold'],
                ];
            @endphp

            @foreach ($academicRecordSubjects as $subject)
                <button
                    type="button"
                    class="ar-subject-card"
                    data-subject="{{ $subject['name'] }}"
                    aria-label="Open {{ $subject['name'] }} student records"
                >
                    <span class="ar-subject-banner ar-tone-{{ $subject['tone'] ?? 'green' }}">
                        <span class="ar-subject-icon"><i class="fa-solid {{ $subject['icon'] ?? 'fa-book-open' }}" aria-hidden="true"></i></span>
                        <span class="ar-subject-banner-label">ACADEMIC RECORDS</span>
                    </span>
                    <span class="ar-subject-card-body">
                        <span class="ar-subject-name">{{ $subject['name'] }}</span>
                        <span class="ar-subject-meta">
                            <i class="fa-solid fa-users" aria-hidden="true"></i>
                            Students in {{ $assignedSectionName ?? 'Grade 7-A' }}
                        </span>
                        <span class="ar-card-open">View students <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                    </span>
                </button>
            @endforeach
        </div>
    </section>

    {{-- STUDENT LIST FOR THE SELECTED SUBJECT --}}
    <section class="ar-view" id="studentRecordsView" hidden>
        <div class="ar-page-intro ar-student-view-intro">
            <div>
                <button type="button" class="ar-back-button" id="backToSubjects">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> All subjects
                </button>
                <p class="ar-eyebrow">ACADEMIC RECORDS</p>
                <h2 id="selectedSubjectTitle">Mathematics</h2>
                <p class="ar-muted">Select a term, then open a student's grades.</p>
            </div>
            <div class="ar-section-chip">
                <i class="fa-solid fa-users" aria-hidden="true"></i>
                <span>Section: <strong>{{ $assignedSectionName ?? 'Grade 7-A' }}</strong></span>
            </div>
        </div>

        <div class="ar-toolbar">
            <div class="ar-term-picker" role="group" aria-label="Choose school term">
                <button type="button" class="ar-term-button is-active" data-term="1st Term" aria-pressed="true">1st Term</button>
                <button type="button" class="ar-term-button" data-term="2nd Term" aria-pressed="false">2nd Term</button>
                <button type="button" class="ar-term-button" data-term="3rd Term" aria-pressed="false">3rd Term</button>
            </div>
            <label class="ar-search">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <span class="sr-only">Search students</span>
                <input type="search" id="studentSearch" placeholder="Search student name or ID..." autocomplete="off">
            </label>
        </div>

        <div class="ar-student-list-card">
            <div class="ar-list-heading">
                <div>
                    <h3>Students</h3>
                    <p class="ar-muted" id="studentListSubtitle">1st Term · Mathematics</p>
                </div>
                <span class="ar-count-pill" id="studentCount">3 students</span>
            </div>

            <div class="ar-table-wrap">
                <table class="ar-student-table">
                    <thead>
                        <tr>
                            <th scope="col">Student</th>
                            <th scope="col">Student ID</th>
                            <th scope="col" class="ar-action-heading">Action</th>
                        </tr>
                    </thead>
                    <tbody id="studentTableBody">
                        {{-- Sample rows for the front-end preview. Replace with real students from the controller. --}}
                        @php
                            $academicRecordStudents = $academicRecordStudents ?? [
                                ['name' => 'Alex Santos', 'id' => '2026-001', 'grades' => ['1st Term' => ['grade' => '90', 'remarks' => 'Passed'], '2nd Term' => ['grade' => '92', 'remarks' => 'Passed'], '3rd Term' => ['grade' => '91', 'remarks' => 'Passed']]],
                                ['name' => 'Maria Reyes', 'id' => '2026-002', 'grades' => ['1st Term' => ['grade' => '87', 'remarks' => 'Passed'], '2nd Term' => ['grade' => '89', 'remarks' => 'Passed'], '3rd Term' => ['grade' => '88', 'remarks' => 'Passed']]],
                                ['name' => 'John Cruz', 'id' => '2026-003', 'grades' => ['1st Term' => ['grade' => null, 'remarks' => 'Pending'], '2nd Term' => ['grade' => null, 'remarks' => 'Pending'], '3rd Term' => ['grade' => null, 'remarks' => 'Pending']]],
                            ];
                        @endphp
                        @foreach ($academicRecordStudents as $student)
                            <tr
                                data-student-name="{{ $student['name'] }}"
                                data-student-id="{{ $student['id'] }}"
                                data-grades="{{ e(json_encode($student['grades'] ?? [])) }}"
                            >
                                <td>
                                    <div class="ar-student-identity">
                                        <span class="ar-avatar" aria-hidden="true">{{ strtoupper(substr($student['name'], 0, 1)) }}</span>
                                        <span class="ar-student-name">{{ $student['name'] }}</span>
                                    </div>
                                </td>
                                <td><span class="ar-student-id">{{ $student['id'] }}</span></td>
                                <td class="ar-action-cell">
                                    <button type="button" class="ar-view-grades-button" data-view-grades>
                                        View Grades <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="ar-empty-state" id="studentEmptyState" hidden>
                <i class="fa-solid fa-user-slash" aria-hidden="true"></i>
                <strong>No students found</strong>
                <span>Try another name or student ID.</span>
            </div>
        </div>
    </section>
</div>

{{-- VIEW GRADES DIALOG: grade and remarks appear only here, not in the student list. --}}
<div class="ar-modal-backdrop" id="gradesModal" hidden>
    <section class="ar-grades-modal" role="dialog" aria-modal="true" aria-labelledby="gradesModalTitle">
        <div class="ar-modal-header">
            <div>
                <p class="ar-eyebrow">STUDENT ACADEMIC RECORD</p>
                <h2 id="gradesModalTitle">View Grades</h2>
                <p class="ar-muted" id="gradesModalSubtitle">Student · Subject · Term</p>
            </div>
            <button type="button" class="ar-modal-close" id="closeGradesModal" aria-label="Close grades">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </div>
        <div class="ar-grade-details">
            <div class="ar-grade-detail-row">
                <span>Student Name</span>
                <strong id="modalStudentName">—</strong>
            </div>
            <div class="ar-grade-detail-row">
                <span>Student ID</span>
                <strong id="modalStudentId">—</strong>
            </div>
            <div class="ar-grade-detail-row">
                <span>Subject</span>
                <strong id="modalSubject">—</strong>
            </div>
            <div class="ar-grade-detail-row">
                <span>Term</span>
                <strong id="modalTerm">—</strong>
            </div>
            <div class="ar-grade-highlight">
                <span>Final Grade</span>
                <strong id="modalFinalGrade">—</strong>
            </div>
            <div class="ar-grade-detail-row">
                <span>Remarks</span>
                <strong id="modalRemarks">—</strong>
            </div>
        </div>
        <p class="ar-readonly-note"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> View-only preview. Editing will be added only if approved by the client.</p>
        <div class="ar-modal-footer">
            <button type="button" class="ar-secondary-button" id="closeGradesModalFooter">Close</button>
        </div>
    </section>
</div>
@endsection

@section('page-js')
    <script src="{{ asset('js/adviser/adviser-academic-records.js') }}" defer></script>
@endsection
