{{-- EXTENDS ADVISER LAYOUT --}}
@extends('layouts.adviser')

@section('title', 'My Students')
@section('header', 'My Students')
@section('header-description', 'View your advisory students and their approved academic records.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/adviser/adviser-students.css') }}">
@endsection

@section('content')

    @php
        $adviserName = trim(implode(' ', array_filter([
            $faculty->first_name,
            $faculty->middle_name,
            $faculty->last_name,
        ])));

        $termLabel = $term === 'overall'
            ? 'Overall'
            : 'Term ' . $term;

        $termsToDisplay = $term === 'overall'
            ? [1, 2, 3]
            : [(int) $term];
    @endphp

    <div class="adviser-students-page">

        {{-- STUDENT SUMMARY --}}
        <div class="students-summary-card">
            <div class="summary-info">
                <span class="summary-label">Total Students</span>
                <h2 id="totalStudents">{{ $totalStudents }}</h2>
                <p>Students in your advisory class</p>
            </div>

            <div class="summary-icon">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
        </div>

        {{-- CLASS INFORMATION --}}
        <div class="students-class-info">
            <div class="class-info-left">
                <div class="class-icon">
                    <i class="fa-solid fa-school"></i>
                </div>

                <div>
                    <h3>{{ $gradeLevel }}</h3>
                    <p>
                        School Year {{ $schoolYear }}
                        · {{ $termLabel }}
                    </p>
                    <small>Adviser: {{ $adviserName ?: 'Name unavailable' }}</small>
                </div>
            </div>

            <span class="class-status">
                <i class="fa-solid fa-circle"></i>
                Active Class
            </span>
        </div>

        {{-- ACADEMIC RECORDS TABLE --}}
        <div class="students-table-card">

            <div class="students-table-header">
                <div>
                    <h3>Advisory Class Academic Records</h3>
                    <p>
                        Approved academic grades of students under {{ $gradeLevel }}.
                    </p>
                </div>
            </div>

            {{-- SEARCH AND FILTER --}}
            <div class="students-toolbar">

                <div class="student-search">
                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input type="text" id="studentSearch" placeholder="Search by student name or ID..."
                        aria-label="Search students">
                </div>

                <select id="genderFilter" aria-label="Filter by gender">
                    <option value="">All Students</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>

                <form method="GET" action="{{ route('adviser.students') }}" class="academic-term-filter">

                    <input type="hidden" name="school_year" value="{{ $schoolYear }}">

                    <select name="term" aria-label="Select academic term" onchange="this.form.submit()">

                        <option value="overall" {{ $term === 'overall' ? 'selected' : '' }}>
                            Overall
                        </option>

                        <option value="1" {{ $term === '1' ? 'selected' : '' }}>
                            Term 1
                        </option>

                        <option value="2" {{ $term === '2' ? 'selected' : '' }}>
                            Term 2
                        </option>

                        <option value="3" {{ $term === '3' ? 'selected' : '' }}>
                            Term 3
                        </option>

                    </select>
                </form>

            </div>

            {{-- SPREADSHEET-STYLE TABLE --}}
            <div class="students-table-wrapper academic-records-wrapper">

                <table class="students-table academic-records-table">

                    <thead>
                        <tr class="academic-subject-header">
                            <th rowspan="2" class="student-id-column">Student ID</th>
                            <th rowspan="2" class="student-name-column">Student Name</th>
                            <th rowspan="2" class="student-grade-column">Grade Level</th>

                            @foreach($subjects as $subject)
                                <th colspan="{{ $term === 'overall' ? 4 : 1 }}" class="academic-subject-heading">
                                    {{ $subject }}
                                </th>
                            @endforeach
                        </tr>

                        <tr class="academic-term-header">
                            @foreach($subjects as $subject)
                                @if($term === 'overall')
                                    <th>Term 1</th>
                                    <th>Term 2</th>
                                    <th>Term 3</th>
                                    <th>Final Grade</th>
                                @else
                                    <th>{{ $termLabel }}</th>
                                @endif
                            @endforeach
                        </tr>
                    </thead>

                    <tbody id="studentsTableBody">

                        @forelse($students as $student)

                            @php
                                $fullName = trim(implode(' ', array_filter([
                                    $student->first_name,
                                    $student->middle_name,
                                    $student->last_name,
                                ])));

                                $initials = strtoupper(
                                    substr($student->first_name ?? '', 0, 1) .
                                    substr($student->last_name ?? '', 0, 1)
                                );

                                $gender = strtolower($student->sex ?? '');
                            @endphp

                            <tr class="academic-student-row" data-gender="{{ $gender }}"
                                data-search="{{ strtolower($student->student_id . ' ' . $fullName) }}">

                                <td class="student-id-column">
                                    {{ $student->student_id }}
                                </td>

                                <td class="student-name-column">
                                    <div class="student-name-cell">
                                        <div class="student-avatar">
                                            {{ $initials }}
                                        </div>

                                        <span>{{ $fullName }}</span>
                                    </div>
                                </td>

                                <td class="student-grade-column">
                                    {{ $student->grade_level }}
                                </td>

                                @foreach($subjects as $subject)

                                    @foreach($termsToDisplay as $displayTerm)

                                        @php
                                            $record = $student->gradeRecords->first(
                                                function ($gradeRecord) use ($subject, $displayTerm) {
                                                    return $gradeRecord->teachingAssignment
                                                        && $gradeRecord->teachingAssignment->subject === $subject
                                                        && (int) $gradeRecord->term === $displayTerm
                                                        && $gradeRecord->status === 'adviser_approved';
                                                }
                                            );
                                        @endphp

                                        <td class="academic-grade-cell">
                                            @if($record && $record->term_grade !== null)
                                                <span class="approved-grade">
                                                    {{ number_format((float) $record->term_grade, 2) }}
                                                </span>
                                            @else
                                                <span class="no-grade">—</span>
                                            @endif
                                        </td>

                                    @endforeach

                                    @if($term === 'overall')
                                        <td class="academic-grade-cell final-grade-cell">
                                            <span class="no-grade">—</span>
                                        </td>
                                    @endif

                                @endforeach

                            </tr>

                        @empty

                            <tr>
                                <td colspan="{{ 3 + ($subjects->count() * ($term === 'overall' ? 4 : 1)) }}"
                                    class="students-empty-state">
                                    <i class="fa-solid fa-user-slash"></i>
                                    <h4>No students found</h4>
                                    <p>
                                        There are no active students assigned to {{ $gradeLevel }}
                                        for school year {{ $schoolYear }}.
                                    </p>
                                </td>
                            </tr>

                        @endforelse

                        {{-- EMPTY SEARCH RESULTS --}}
                        <tr id="studentsEmpty" hidden>
                            <td colspan="{{ 3 + ($subjects->count() * ($term === 'overall' ? 4 : 1)) }}"
                                class="students-empty-state">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <h4>No matching students found</h4>
                                <p>Try another student name, ID, or gender filter.</p>
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

            <div class="students-table-footer">
                <span id="studentCount">
                    Showing {{ $totalStudents }}
                    {{ $totalStudents === 1 ? 'student' : 'students' }}
                </span>

                <span>
                    {{ $gradeLevel }} · {{ $schoolYear }}
                </span>
            </div>

            @if($subjects->isEmpty())
                <div class="academic-no-subjects">
                    <i class="fa-solid fa-book-open"></i>
                    <p>No subjects have been assigned to this grade level yet.</p>
                </div>
            @endif

            <div class="academic-records-note">
                <i class="fa-solid fa-circle-info"></i>
                Only Adviser-approved grades are displayed. Final Grade will remain blank
                until the school's approved final-grade computation is implemented.
            </div>

        </div>

    </div>

@endsection

@section('page-js')
    <script src="{{ asset('js/adviser/adviser-students.js') }}"></script>
@endsection