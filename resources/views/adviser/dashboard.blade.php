{{-- EXTENDS ADVISER LAYOUT --}}
@extends('layouts.adviser')

{{-- PAGE TITLE --}}
@section('title', 'Adviser Dashboard')

{{-- PAGE HEADER --}}
@section('header', 'Adviser Dashboard')

{{-- PAGE HEADER DESCRIPTION --}}
@section('header-description', 'Manage your class, review grade submissions, and monitor academic records.')

{{-- PAGE CSS --}}
@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/adviser/adviser-dashboard.css') }}">
@endsection

{{-- MAIN CONTENT --}}
@section('content')

    <section class="adviser-dashboard">

        {{-- WELCOME CARD --}}
        <div class="welcome-card">
            <div class="welcome-text">
                <span>FORBES ACADEMY</span>

                <h2>Welcome {{ Auth::user()?->name ?? 'Adviser' }}!</h2>

                <p>
                    Manage students, teachers, subjects, classes, and academic
                    records in one place.
                </p>

                <div class="welcome-details">
                    <div class="welcome-detail">
                        <i class="fa-solid fa-calendar"></i>
                        <div>
                            <small>School Year</small>
                            <strong>{{ str_replace('-', ' - ', $schoolYear) }}</strong>
                        </div>
                    </div>

                    <div class="welcome-divider"></div>

                    <div class="welcome-detail">
                        <i class="fa-solid fa-book-open"></i>
                        <div>
                            <small>Term</small>
                            <strong>
                                {{ $term == 1 ? '1st Term' : ($term == 2 ? '2nd Term' : '3rd Term') }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>


            <div class="welcome-icon">
                <img src="{{ asset('images/forbesologo.png') }}" alt="Forbes Academy Logo">
            </div>

        </div>

        {{-- DASHBOARD STATISTICS --}}
        <div class="adviser-stats">

            <div class="adviser-stat-card">
                <div class="stat-icon green">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <div class="stat-details">
                    <p>Assigned Classes</p>
                    <h3>{{ $assignedClasses->count() }}</h3>
                    <span>Classes handled</span>
                </div>
            </div>

            <div class="adviser-stat-card">
                <div class="stat-icon blue">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-details">
                    <p>Total Students</p>
                    <h3>{{ $totalStudents }}</h3>
                    <span>Across assigned classes</span>
                </div>
            </div>

            <div class="adviser-stat-card">
                <div class="stat-icon orange">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="stat-details">
                    <p>Pending Reviews</p>
                    <h3>{{ $pendingReviews }}</h3>
                    <span>Awaiting your review</span>
                </div>
            </div>

            <div class="adviser-stat-card">
                <div class="stat-icon purple">
                    <i class="fa-solid fa-paper-plane"></i>
                </div>
                <div class="stat-details">
                    <p>Forwarded to Admin</p>
                    <h3>{{ $forwardedToAdmin }}</h3>
                    <span>Submitted for approval</span>
                </div>
            </div>

        </div>



        {{-- GRADE SUBMISSION PROGRESS --}}
        <div class="adviser-panel grade-progress-panel">

            <div class="panel-header">
                <div>
                    <h3>Grade Submission Progress</h3>
                    <p>Monitor teacher grade submissions for your advisory classes.</p>
                </div>

                {{-- TERM FILTER --}}
                <form method="GET" action="{{ route('adviser.dashboard') }}" class="grade-term-filter">

                    <label for="gradeTerm">Academic Term</label>

                    <select name="term" id="gradeTerm" onchange="this.form.submit()">
                        <option value="1" {{ $term == 1 ? 'selected' : '' }}>1st Term</option>
                        <option value="2" {{ $term == 2 ? 'selected' : '' }}>2nd Term</option>
                        <option value="3" {{ $term == 3 ? 'selected' : '' }}>3rd Term</option>
                    </select>
                </form>
            </div>

            @forelse($assignedClasses as $class)

                <div class="grade-progress-class">

                    {{-- CLASS HEADER --}}
                    <div class="grade-progress-class-header">

                        <div class="grade-progress-class-info">
                            <div class="grade-progress-class-icon">
                                <i class="fa-solid fa-chalkboard-user"></i>
                            </div>

                            <div>
                                <h4>
                                    {{ $class->grade_level }}
                                </h4>

                                <span>
                                    {{ $class->student_count }}
                                    {{ $class->student_count === 1 ? 'Student' : 'Students' }}
                                    <span class="progress-dot">•</span>
                                    {{ $class->total_subjects }}
                                    {{ $class->total_subjects === 1 ? 'Subject' : 'Subjects' }}
                                </span>
                            </div>
                        </div>

                        <div class="grade-progress-summary">
                            <strong>{{ $class->progress_percentage }}%</strong>
                            <span>Completed</span>
                        </div>

                    </div>

                    {{-- OVERALL PROGRESS BAR --}}
                    <div class="grade-progress-bar">
                        <div class="grade-progress-fill" style="width: {{ $class->progress_percentage }}%;">
                        </div>
                    </div>

                    {{-- SUBJECT SUMMARY --}}
                    <div class="grade-progress-counts">
                        <span class="progress-count submitted-count">
                            <i class="fa-solid fa-circle-check"></i>
                            {{ $class->submitted_subjects }} Submitted
                        </span>

                        <span class="progress-count partial-count">
                            <i class="fa-solid fa-circle-half-stroke"></i>
                            {{ $class->partial_subjects }} Partial
                        </span>

                        <span class="progress-count not-submitted-count">
                            <i class="fa-regular fa-circle"></i>
                            {{ $class->not_submitted_subjects }} Not Submitted
                        </span>
                    </div>

                    {{-- EXPANDABLE SUBJECT LIST --}}
                    {{-- EXPANDABLE SUBJECT LIST --}}
                    <div class="grade-progress-details">

                        <button type="button" class="grade-progress-toggle" aria-expanded="false">

                            <span>View Subject Details</span>
                            <i class="fa-solid fa-chevron-down"></i>

                        </button>

                        <div class="grade-progress-content">
                            <div class="grade-progress-content-inner">

                                {{-- EXISTING SUBJECT LIST --}}
                                @forelse($class->subjectProgress as $subject)

                                    @php
                                        $teacher = $subject->faculty;

                                        $teacherName = $teacher
                                            ? trim(implode(' ', array_filter([
                                                $teacher->first_name,
                                                $teacher->middle_name,
                                                $teacher->last_name,
                                            ])))
                                            : 'No Teacher Assigned';

                                        $statusClass = match ($subject->status) {
                                            'Submitted' => 'submitted',
                                            'Partial' => 'partial',
                                            default => 'not-submitted',
                                        };
                                    @endphp

                                    <div class="grade-progress-subject">

                                        <div class="grade-subject-info">
                                            <div class="grade-subject-icon">
                                                <i class="fa-solid fa-book-open"></i>
                                            </div>

                                            <div>
                                                <h5>{{ $subject->subject }}</h5>
                                                <span>{{ $teacherName }}</span>
                                            </div>
                                        </div>

                                        <div class="grade-subject-submission">
                                            <span class="grade-submission-count">
                                                {{ $subject->submitted_count }}
                                                / {{ $subject->total_students }} Students
                                            </span>

                                            <span class="grade-progress-status {{ $statusClass }}">
                                                {{ $subject->status }}
                                            </span>
                                        </div>

                                    </div>

                                @empty

                                    <div class="grade-progress-empty">
                                        <i class="fa-solid fa-book"></i>
                                        <p>No Subjects Assigned</p>
                                        <span>No teaching assignments were found for this grade level.</span>
                                    </div>

                                @endforelse

                            </div>
                        </div>

                    </div>

                </div>

            @empty

                <div class="grade-progress-empty">
                    <i class="fa-solid fa-chalkboard"></i>
                    <p>No Advisory Classes Found</p>
                    <span>
                        Your grade submission progress will appear once an advisory
                        class is assigned to you.
                    </span>
                </div>

            @endforelse

        </div>


        {{-- RECENT GRADE SUBMISSIONS --}}
        <div class="adviser-panel">
            <div class="panel-header">
                <div>
                    <h3>Recent Grade Submissions</h3>
                    <p>Review the latest grade submissions from your teachers.</p>
                </div>

                <a href="{{ route('adviser.grade-review') }}" class="view-all-link">
                    View All
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="adviser-table-wrapper">
                <table class="adviser-table">
                    <thead>
                        <tr>
                            <th>Class / Subject</th>
                            <th>Teacher</th>
                            <th>Date Submitted</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($recentSubmissions as $submission)

                                        @php
                                            $assignment = $submission->teachingAssignment;
                                            $teacher = $assignment?->faculty;

                                            $teacherName = $teacher
                                                ? trim(
                                                    $teacher->first_name . ' ' .
                                                    ($teacher->middle_name ? $teacher->middle_name . ' ' : '') .
                                                    $teacher->last_name
                                                )
                                                : 'Unknown Teacher';
                                        @endphp

                                        <tr>
                                            <td>
                                                <div class="class-name">
                                                    {{ $assignment?->grade_level ?? 'Unknown Class' }}
                                                </div>
                                                <span class="subject-name">
                                                    {{ $assignment?->subject ?? 'Unknown Subject' }}
                                                    •
                                                    {{ $submission->term }}{{ $submission->term == 1 ? 'st' : ($submission->term == 2 ? 'nd' : 'rd') }}
                                                    Term
                                                </span>
                                            </td>

                                            <td>{{ $teacherName }}</td>

                                            <td>
                                                {{ $submission->submitted_at
                            ? \Carbon\Carbon::parse($submission->submitted_at)->format('M d, Y')
                            : 'N/A' }}
                                            </td>

                                            <td>
                                                <span class="status-badge pending">
                                                    Pending Review
                                                </span>
                                            </td>

                                            <td>
                                                <a href="{{ route('adviser.grade-review') }}" class="action-link">
                                                    Review
                                                </a>
                                            </td>
                                        </tr>

                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <i class="fa-solid fa-inbox"></i>
                                    <p>No Grade Submissions Yet</p>
                                    <span>
                                        Teacher submissions will appear here once submitted.
                                    </span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- BOTTOM DASHBOARD SECTIONS --}}
        <div class="adviser-bottom-grid">

            {{-- ASSIGNED CLASSES --}}
            <div class="adviser-panel">
                <div class="panel-header">
                    <div>
                        <h3>My Assigned Classes</h3>
                        <p>Your current class assignments.</p>
                    </div>
                </div>

                @forelse($assignedClasses as $class)

                    <div class="assigned-class-item">
                        <div class="assigned-class-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>

                        <div class="assigned-class-info">
                            <h4>
                                {{ $class->grade_level }}
                            </h4>
                            <p>
                                {{ $class->student_count }}
                                {{ $class->student_count === 1 ? 'Student' : 'Students' }}
                            </p>
                        </div>

                        <i class="fa-solid fa-chevron-right class-arrow"></i>
                    </div>

                @empty
                    <div class="empty-state">
                        <i class="fa-solid fa-chalkboard"></i>
                        <<p>No Assigned Grade Level</p>
                            <span>Your assigned grade level will appear here once an administrator assigns you as an
                                adviser.</span>
                    </div>
                @endforelse
            </div>

            {{-- QUICK ACTIONS --}}
            <div class="adviser-panel">
                <div class="panel-header">
                    <div>
                        <h3>Quick Actions</h3>
                        <p>Access your frequently used pages.</p>
                    </div>
                </div>

                <a href="{{ route('adviser.grade-review') }}" class="quick-action-item">
                    <div class="quick-action-icon">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h4>Review Grade Submissions</h4>
                        <p>Check grades submitted by teachers.</p>
                    </div>
                    <i class="fa-solid fa-chevron-right quick-arrow"></i>
                </a>

                <a href="{{ route('adviser.classes') }}" class="quick-action-item">
                    <div class="quick-action-icon">
                        <i class="fa-solid fa-chalkboard"></i>
                    </div>
                    <div>
                        <h4>View My Classes</h4>
                        <p>Manage your assigned classes.</p>
                    </div>
                    <i class="fa-solid fa-chevron-right quick-arrow"></i>
                </a>

                <a href="{{ url('/adviser/announcements') }}" class="quick-action-item">
                    <div class="quick-action-icon">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                    <div>
                        <h4>Announcements</h4>
                        <p>Read the latest school updates.</p>
                    </div>
                    <i class="fa-solid fa-chevron-right quick-arrow"></i>
                </a>
            </div>

        </div>

    </section>

@endsection

{{-- PAGE JAVASCRIPT --}}
@section('page-js')

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('.grade-progress-toggle').forEach(button => {

                button.addEventListener('click', function () {

                    const content = this.nextElementSibling;
                    const isOpen = this.classList.contains('active');

                    if (isOpen) {

                        // SMOOTH CLOSE
                        content.style.height = content.scrollHeight + 'px';

                        content.offsetHeight;

                        content.style.height = '0px';
                        content.classList.remove('show');

                        this.classList.remove('active');
                        this.setAttribute('aria-expanded', 'false');

                    } else {

                        // SMOOTH OPEN
                        content.classList.add('show');
                        content.style.height = content.scrollHeight + 'px';

                        this.classList.add('active');
                        this.setAttribute('aria-expanded', 'true');

                        const handleTransition = function (event) {

                            if (event.propertyName !== 'height') return;

                            if (content.classList.contains('show')) {
                                content.style.height = 'auto';
                            }

                            content.removeEventListener('transitionend', handleTransition);
                        };

                        content.addEventListener('transitionend', handleTransition);

                    }

                });

            });

        });
    </script>

@endsection