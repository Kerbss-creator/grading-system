{{-- EXTENDS ADVISER LAYOUT --}}
@extends('layouts.adviser')

@section('title', 'My Classes')
@section('header', 'My Classes')
@section('header-description', 'View your advisory class and monitor student records and grade submissions.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/adviser/adviser-classes.css') }}">
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

        $visibleReviews = $recentGradeReviews->unique(function ($review) {
            return $review->teaching_assignment_id . '-' . $review->term;
        });
    @endphp

    <div class="adviser-classes-page">

        {{-- ADVISORY CLASS PROFILE --}}
        <section class="adviser-class-profile">

            <div class="class-profile-top">

                <div class="class-profile-icon">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>

                <div class="class-profile-info">
                    <span class="class-profile-label">MY ADVISORY CLASS</span>
                    <h2>{{ $gradeLevel }}</h2>
                    <p>School Year {{ $schoolYear }}</p>
                </div>

                <div class="class-status">
                    <span class="status-dot"></span>
                    Active Class
                </div>

            </div>

            <div class="class-profile-divider"></div>

            {{-- CLASS DETAILS --}}
            <div class="class-details-grid">

                <div class="class-detail-item">
                    <span class="detail-icon">
                        <i class="fa-solid fa-users"></i>
                    </span>

                    <div>
                        <span class="detail-label">Grade Level</span>
                        <h4>{{ $gradeLevel }}</h4>
                    </div>
                </div>

                <div class="class-detail-item">
                    <span class="detail-icon">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </span>

                    <div>
                        <span class="detail-label">Assigned Adviser</span>
                        <h4>{{ $adviserName ?: 'Name unavailable' }}</h4>
                    </div>
                </div>

                <div class="class-detail-item">
                    <span class="detail-icon">
                        <i class="fa-solid fa-calendar-days"></i>
                    </span>

                    <div>
                        <span class="detail-label">Academic Period</span>
                        <h4>{{ $termLabel }}</h4>
                    </div>
                </div>

            </div>

        </section>

        {{-- CLASS SUMMARY CARDS --}}
        <section class="class-summary-grid">

            <div class="class-summary-card">

                <div class="summary-card-top">
                    <span class="summary-icon students-icon">
                        <i class="fa-solid fa-users"></i>
                    </span>

                    <span class="summary-caption">My Class</span>
                </div>

                <h3>{{ $totalStudents }}</h3>
                <p>Total Students</p>

                <a href="{{ url('/adviser/students') }}" class="summary-card-link">
                    View student list
                    <i class="fa-solid fa-arrow-right"></i>
                </a>

            </div>

            <div class="class-summary-card">

                <div class="summary-card-top">
                    <span class="summary-icon pending-icon">
                        <i class="fa-solid fa-file-circle-exclamation"></i>
                    </span>

                    <span class="summary-caption">Needs Attention</span>
                </div>

                <h3>{{ $pendingReviews }}</h3>
                <p>Pending Grade Reviews</p>

                <a href="{{ url('/adviser/grade-submissions') }}" class="summary-card-link">
                    Review submissions
                    <i class="fa-solid fa-arrow-right"></i>
                </a>

            </div>

        </section>

        {{-- CLASS QUICK ACTIONS --}}
        <section class="adviser-panel">

            <div class="panel-heading">
                <div>
                    <h3>Class Activities</h3>
                    <p>Quick access to your advisory class tasks.</p>
                </div>
            </div>

            <div class="class-quick-actions">

                <a href="{{ url('/adviser/students') }}" class="class-action-item">

                    <span class="class-action-icon">
                        <i class="fa-solid fa-users"></i>
                    </span>

                    <span class="class-action-text">
                        <strong>View My Students</strong>
                        <small>View students assigned to your class.</small>
                    </span>

                    <i class="fa-solid fa-chevron-right action-arrow"></i>

                </a>

                <a href="{{ url('/adviser/grade-submissions') }}" class="class-action-item">

                    <span class="class-action-icon review-action-icon">
                        <i class="fa-solid fa-file-circle-check"></i>
                    </span>

                    <span class="class-action-text">
                        <strong>Review Grade Submissions</strong>
                        <small>Check grades submitted by subject teachers.</small>
                    </span>

                    <i class="fa-solid fa-chevron-right action-arrow"></i>

                </a>

            </div>

        </section>

        {{-- ADVISORY CLASS ACADEMIC RECORDS --}}
        <section class="adviser-panel advisory-records-panel">

            <div class="panel-heading">
                <div>
                    <h3>Advisory Class Academic Records</h3>
                    <p>View the academic records of students in your assigned grade level.</p>
                </div>
            </div>

            {{-- TERM FILTER --}}
            <form method="GET" action="{{ route('adviser.classes') }}" class="grade-review-toolbar">

                <input type="hidden" name="school_year" value="{{ $schoolYear }}">

                <div class="grade-filter-box">
                    <i class="fa-solid fa-calendar-days"></i>

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
                </div>

            </form>

            <div class="advisory-records-table-wrapper">

                <table class="advisory-records-table">

                    <thead>
                        <tr>
                            <th rowspan="2">Student ID</th>
                            <th rowspan="2">Student Name</th>
                            <th rowspan="2">Grade Level</th>

                            @forelse($subjects as $subject)
                                <th colspan="{{ $term === 'overall' ? 4 : 1 }}" class="subject-group-heading">
                                    {{ $subject }}
                                </th>
                            @empty
                                <th rowspan="2">Subjects</th>
                            @endforelse
                        </tr>

                        <tr>
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

                    <tbody>

                        @forelse($students as $student)

                            <tr>
                                <td>{{ $student->student_id }}</td>

                                <td>
                                    <strong>
                                        {{ $student->last_name }},
                                        {{ $student->first_name }}
                                        {{ $student->middle_name }}
                                    </strong>
                                </td>

                                <td>{{ $student->grade_level }}</td>

                                @forelse($subjects as $subject)

                                    @php
                                        $termsToDisplay = $term === 'overall'
                                            ? [1, 2, 3]
                                            : [(int) $term];
                                    @endphp

                                    @foreach($termsToDisplay as $displayTerm)

                                        @php
                                            $record = $student->gradeRecords->first(
                                                function ($gradeRecord) use ($subject, $displayTerm) {
                                                    return $gradeRecord->teachingAssignment
                                                        && $gradeRecord->teachingAssignment->subject === $subject
                                                        && (int) $gradeRecord->term === $displayTerm
                                                        && (
                                                            $gradeRecord->status === 'submitted'
                                                            || $gradeRecord->adviser_reviewed_at !== null
                                                        );
                                                }
                                            );
                                        @endphp

                                        <td>
                                            @if($record && $record->term_grade !== null)
                                                {{ number_format((float) $record->term_grade, 2) }}
                                            @else
                                                —
                                            @endif
                                        </td>

                                    @endforeach

                                    @if($term === 'overall')
                                        <td>—</td>
                                    @endif

                                @empty
                                    <td>—</td>
                                @endforelse

                            </tr>

                        @empty

                            <tr>
                                <td colspan="{{ 3 + ($subjects->count() * ($term === 'overall' ? 4 : 1)) }}"
                                    class="empty-review-message">
                                    <i class="fa-solid fa-users"></i>
                                    <p>No active students found for {{ $gradeLevel }}.</p>
                                </td>
                            </tr>

                        @endforelse

                        @if($students->isNotEmpty() && $subjects->isEmpty())
                            <tr>
                                <td colspan="4" class="empty-review-message">
                                    <i class="fa-solid fa-book-open"></i>
                                    <p>No subjects are assigned to this grade level yet.</p>
                                </td>
                            </tr>
                        @endif

                    </tbody>

                </table>

            </div>

            <div class="grade-review-footer">
                <span>{{ $totalStudents }} students</span>
                <span>Final Grade is pending the approved computation policy.</span>
            </div>

        </section>

        {{-- RECENT GRADE REVIEWS --}}
        <section class="adviser-panel grade-review-panel">

            <div class="panel-heading grade-review-heading">

                <div>
                    <h3>Recent Grade Reviews</h3>
                    <p>Monitor grade submissions for your advisory class.</p>
                </div>

                <a href="{{ url('/adviser/grade-submissions') }}" class="view-all-link">
                    View All
                    <i class="fa-solid fa-arrow-right"></i>
                </a>

            </div>

            {{-- SEARCH AND STATUS FILTER --}}
            <div class="grade-review-toolbar">

                <div class="grade-search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input type="text" id="gradeReviewSearch" placeholder="Search subject or teacher..."
                        aria-label="Search grade reviews">
                </div>

                <div class="grade-filter-box">
                    <i class="fa-solid fa-filter"></i>

                    <select id="gradeReviewFilter" aria-label="Filter grade reviews">
                        <option value="all">All Status</option>
                        <option value="pending">Pending Review</option>
                        <option value="reviewed">Reviewed</option>
                    </select>
                </div>

            </div>

            {{-- GRADE REVIEW TABLE --}}
            <div class="grade-review-table-wrapper">

                <table class="grade-review-table">

                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Subject Teacher</th>
                            <th>Term</th>
                            <th>Date Submitted</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody id="gradeReviewTableBody">

                        @forelse($visibleReviews as $review)

                                        @php
                                            $isReviewed = $review->adviser_reviewed_at !== null;
                                            $reviewStatus = $isReviewed ? 'reviewed' : 'pending';

                                            $teacher = $review->teachingAssignment?->faculty;

                                            $teacherName = $teacher
                                                ? trim(implode(' ', array_filter([
                                                    $teacher->first_name,
                                                    $teacher->middle_name,
                                                    $teacher->last_name,
                                                ])))
                                                : 'Unknown Teacher';

                                            $subjectName = $review->teachingAssignment?->subject ?? 'Unknown Subject';
                                        @endphp

                                        <tr class="grade-review-row" data-search="{{ strtolower($subjectName . ' ' . $teacherName) }}"
                                            data-status="{{ $reviewStatus }}">

                                            <td>
                                                <div class="subject-cell">
                                                    <span class="subject-icon">
                                                        <i class="fa-solid fa-book"></i>
                                                    </span>

                                                    <div>
                                                        <strong>{{ $subjectName }}</strong>
                                                        <small>{{ $gradeLevel }}</small>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>{{ $teacherName }}</td>

                                            <td>Term {{ $review->term }}</td>

                                            <td>
                                                {{ $review->submitted_at
                            ? $review->submitted_at->format('M d, Y')
                            : '—' }}
                                            </td>

                                            <td>
                                                @if($isReviewed)
                                                    <span class="grade-status status-reviewed">
                                                        <span></span>
                                                        Reviewed
                                                    </span>
                                                @else
                                                    <span class="grade-status status-pending">
                                                        <span></span>
                                                        Pending Review
                                                    </span>
                                                @endif
                                            </td>

                                            <td>
                                                <a href="{{ url('/adviser/grade-submissions') }}" class="review-action-link">
                                                    {{ $isReviewed ? 'View' : 'Review' }}
                                                </a>
                                            </td>

                                        </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="empty-review-message">
                                    <i class="fa-solid fa-folder-open"></i>
                                    <p>No grade submissions found for this advisory class.</p>
                                </td>
                            </tr>

                        @endforelse

                        <tr id="gradeReviewEmpty" style="display: none;">
                            <td colspan="6" class="empty-review-message">
                                <i class="fa-solid fa-folder-open"></i>
                                <p>No matching grade submissions found.</p>
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

            <div class="grade-review-footer">
                <span id="gradeReviewCount">
                    Showing {{ $visibleReviews->count() }}
                    {{ $visibleReviews->count() === 1 ? 'submission' : 'submissions' }}
                </span>
            </div>

        </section>

    </div>

@endsection

@section('page-js')
    <script src="{{ asset('js/adviser/adviser-classes.js') }}"></script>
@endsection