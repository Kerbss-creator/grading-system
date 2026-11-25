@extends('layouts.teacher')

@section('title', 'Forbes Academy Dashboard')

@section('header', 'My Dashboard')

@section('header-description', 'Welcome, Teacher!')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/teacher/teacher-dashboard.css') }}">
@endsection

@section('content')

    <section class="dashboard-container">

        {{-- WELCOME CARD --}}
        <div class="welcome-card">

            <div class="welcome-text">

                <span>FORBES ACADEMY</span>

                <h2>Welcome {{ Auth::user()?->name ?? 'Teacher' }}!</h2>

                <p>
                    Manage your classes, grades, and academic records
                    from one place.
                </p>

                <div class="welcome-details">

                    <div class="welcome-detail">
                        <i class="fa-solid fa-calendar"></i>

                        <div>
                            <small>School Year</small>
                            <strong>
                                {{ $schoolYear ?? 'No Active School Year' }}
                            </strong>
                        </div>
                    </div>

                    <div class="welcome-divider"></div>

                    <div class="welcome-detail">
                        <i class="fa-solid fa-book-open"></i>

                        <div>
                            <small>Active Term</small>
                            <strong>
                                @if($term)
                                    {{ $term == 1 ? '1st Term' : ($term == 2 ? '2nd Term' : '3rd Term') }}
                                @else
                                    No Active Term
                                @endif
                            </strong>
                        </div>
                    </div>

                </div>

            </div>

            <div class="welcome-icon">
                <img src="{{ asset('images/forbesologo.png') }}" alt="Forbes Academy Logo">
            </div>

        </div>


        {{-- OVERVIEW --}}
        <div class="section-header">
            <h2>Overview</h2>
        </div>

        <div class="stats-grid">

            {{-- MY CLASSES --}}
            <div class="stat-card">

                <div class="stat-icon student">
                    <i class="fa-solid fa-chalkboard"></i>
                </div>

                <div class="stat-info">
                    <span>My Subjects</span>
                    <h3>{{ $totalClasses }}</h3>
                    <small>Assigned subjects</small>
                </div>

                <i class="fa-solid fa-chevron-right stat-arrow"></i>

            </div>


            {{-- TOTAL STUDENTS --}}
            <div class="stat-card">

                <div class="stat-icon teacher">
                    <i class="fa-solid fa-users"></i>
                </div>

                <div class="stat-info">
                    <span>Total Students</span>
                    <h3>{{ $totalStudents }}</h3>
                    <small>Unique active students</small>
                </div>

                <i class="fa-solid fa-chevron-right stat-arrow"></i>

            </div>


            {{-- PENDING GRADES --}}
            <div class="stat-card">

                <div class="stat-icon class">
                    <i class="fa-solid fa-file-circle-exclamation"></i>
                </div>

                <div class="stat-info">
                    <span>Pending Grades</span>
                    <h3>{{ $pendingGrades }}</h3>
                    <small>Incomplete student grades</small>
                </div>

                <i class="fa-solid fa-chevron-right stat-arrow"></i>

            </div>


            {{-- COMPLETED GRADES --}}
            <div class="stat-card">

                <div class="stat-icon subject">
                    <i class="fa-solid fa-circle-check"></i>
                </div>

                <div class="stat-info">
                    <span>Completed Grades</span>
                    <h3>{{ $completedGrades }}</h3>
                    <small>Complete student assessments</small>
                </div>

                <i class="fa-solid fa-chevron-right stat-arrow"></i>

            </div>

        </div>


        {{-- MY CLASSES + QUICK ACTIONS --}}
        <div class="dashboard-grid">

            {{-- MY CLASSES --}}
            <div class="content-card">

                <div class="card-header">

                    <div class="card-title">

                        <div class="card-title-icon">
                            <i class="fa-solid fa-chalkboard"></i>
                        </div>

                        <div>
                            <h2>My Classes</h2>
                            <p>Subjects currently assigned to you</p>
                        </div>

                    </div>

                </div>


                <div class="class-list">

                    @forelse($assignedClasses as $assignment)

                        <a href="{{ route('teacher.grade-management') }}" class="class-item">

                            <div class="class-subject-icon">
                                <i class="fa-solid fa-book-open"></i>
                            </div>

                            <div class="class-info">

                                <h3>
                                    {{ $assignment->subjectDetails?->subject_name ?? $assignment->subject }}
                                </h3>

                                <span>
                                    Grade {{ $assignment->grade_level }}
                                </span>

                            </div>

                            <div class="class-students">

                                <i class="fa-solid fa-users"></i>

                                <div>
                                    <strong>{{ $assignment->total_students }}</strong>
                                    <small>Students</small>
                                </div>

                            </div>

                            <i class="fa-solid fa-chevron-right class-arrow"></i>

                        </a>

                    @empty

                        <div class="empty-state">
                            <i class="fa-solid fa-book-open"></i>
                            <h3>No Assigned Classes</h3>
                            <p>
                                You currently have no subject assignments
                                for the active school year.
                            </p>
                        </div>

                    @endforelse

                </div>

            </div>


            {{-- QUICK ACTIONS --}}
            <div class="content-card">

                <div class="card-header">

                    <div class="card-title">

                        <div>
                            <h2>Quick Actions</h2>
                            <p>Common teacher activities</p>
                        </div>

                    </div>

                </div>


                <div class="quick-actions">

                    <a href="{{ route('teacher.grade-management') }}" class="quick-action-btn">

                        <i class="fa-solid fa-plus"></i>
                        <span>Enter Grades</span>
                        <i class="fa-solid fa-chevron-right"></i>

                    </a>


                    <a href="{{ route('teacher.grade-review') }}" class="quick-action-btn">

                        <i class="fa-solid fa-file-lines"></i>
                        <span>View Grade Records</span>
                        <i class="fa-solid fa-chevron-right"></i>

                    </a>

                </div>

            </div>

        </div>


        {{-- GRADE SUBMISSION + RECENT ACTIVITIES --}}
        <div class="dashboard-grid lower-grid">

            {{-- GRADE SUBMISSION --}}
            <div class="content-card">

                <div class="card-header">

                    <div class="card-title">

                        <div class="card-title-icon">
                            <i class="fa-solid fa-file-pen"></i>
                        </div>

                        <div>
                            <h2>Grade Submission</h2>
                            <p>
                                Submission progress for
                                {{ $term ? 'Term ' . $term : 'the active term' }}
                            </p>
                        </div>

                    </div>

                </div>


                <div class="table-container">

                    <table>

                        <thead>
                            <tr>
                                <th>Class / Subject</th>
                                <th>Submitted</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($assignedClasses as $assignment)

                                @php
                                    $total = $assignment->total_students;
                                    $submitted = $assignment->submitted_students;

                                    if ($total > 0 && $submitted >= $total) {
                                        $submissionStatus = 'Completed';
                                    } elseif ($submitted > 0) {
                                        $submissionStatus = 'Partial';
                                    } else {
                                        $submissionStatus = 'Pending';
                                    }
                                @endphp

                                <tr>

                                    <td>
                                        <div class="class-table-name">

                                            <strong>
                                                Grade {{ $assignment->grade_level }}
                                            </strong>

                                            <span>
                                                {{ $assignment->subjectDetails?->subject_name ?? $assignment->subject }}
                                            </span>

                                        </div>
                                    </td>

                                    <td>
                                        {{ $submitted }} / {{ $total }}
                                    </td>

                                    <td>

                                        @if($submissionStatus === 'Completed')

                                            <span class="status completed">
                                                <i class="fa-solid fa-circle-check"></i>
                                                Completed
                                            </span>

                                        @elseif($submissionStatus === 'Partial')

                                            <span class="status pending">
                                                <i class="fa-solid fa-clock"></i>
                                                Partial
                                            </span>

                                        @else

                                            <span class="status pending">
                                                <i class="fa-solid fa-clock"></i>
                                                Pending
                                            </span>

                                        @endif

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="3" class="empty-table">
                                        No grade submission records available.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>


            {{-- RECENT ACTIVITIES --}}
            <div class="content-card">

                <div class="card-header">

                    <div class="card-title">

                        <div class="card-title-icon">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>

                        <div>
                            <h2>Recent Activities</h2>
                            <p>Your latest recorded activities</p>
                        </div>

                    </div>

                </div>


                <div class="activity-list">

                    @forelse($recentActivities as $activity)

                        <div class="activity-item">

                            <div class="activity-icon">
                                <i class="fa-solid fa-clock"></i>
                            </div>

                            <div class="activity-info">

                                <p>
                                    <strong>
                                        {{ ucwords(str_replace('_', ' ', $activity->action ?? 'Activity')) }}
                                    </strong>

                                    @if($activity->record_type)
                                        {{ ' - ' . ucwords(str_replace('_', ' ', $activity->record_type)) }}
                                    @endif

                                    @if($activity->record_key)
                                        <strong>{{ $activity->record_key }}</strong>
                                    @endif
                                </p>

                                <span>
                                    {{ $activity->created_at?->format('M d, Y - h:i A') ?? 'Date unavailable' }}
                                </span>

                            </div>

                        </div>

                    @empty

                        <div class="empty-state">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <h3>No Recent Activities</h3>
                            <p>
                                Your recorded activities will appear here.
                            </p>
                        </div>

                    @endforelse

                </div>

            </div>

        </div>


        {{-- ANNOUNCEMENTS --}}
        <div class="content-card recent-activity-card announcements-card">

            <div class="card-header">

                <div class="card-title">

                    <div class="card-title-icon">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>

                    <div>
                        <h2>Announcements</h2>
                        <p>Latest school announcements</p>
                    </div>

                </div>

            </div>


            <div class="announcement-list">

                @forelse($announcements as $announcement)

                    <div class="announcement-item">

                        <div class="announcement-icon">
                            <i class="fa-solid fa-bullhorn"></i>
                        </div>

                        <div class="announcement-info">

                            <h3>{{ $announcement->title }}</h3>

                            <p>
                                {{ $announcement->message }}
                            </p>

                            <span>
                                {{ $announcement->created_at?->format('F d, Y') ?? 'Date unavailable' }}
                            </span>

                        </div>

                    </div>

                @empty

                    <div class="empty-state">
                        <i class="fa-solid fa-bullhorn"></i>
                        <h3>No Announcements</h3>
                        <p>
                            There are currently no announcements for teachers.
                        </p>
                    </div>

                @endforelse

            </div>

        </div>

    </section>

@endsection


@section('page-js')
    <script src="{{ asset('js/dashboards.js') }}"></script>
@endsection