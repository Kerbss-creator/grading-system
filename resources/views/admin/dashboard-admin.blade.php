@extends('layouts.admin')

@section('title', 'Forbes Academy Dashboard')

@section('header', 'Dashboard')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
@endsection

@section('content')

    @php
        $gradeLevelLabels = ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'];
        $gradeLevelCounts = $studentsByGrade->pluck('count', 'grade_level');
        $maxGradeCount = max(1, (int) $gradeLevelCounts->max());
    @endphp

    <section class="dashboard-container">

        {{-- =========================================
        WELCOME CARD
        ========================================= --}}
        <div class="welcome-card">

            <div class="welcome-text">

                <span>FORBES ACADEMY</span>

                <h2>Welcome, {{ Auth::user()->name ?? 'Administrator' }}!</h2>

                <p>
                    Manage students, faculty, subjects, and academic
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
                            <strong>{{ $termOptions[$term] }}</strong>
                        </div>
                    </div>

                </div>

            </div>

            <div class="welcome-icon">
                <img src="{{ asset('images/forbesologo.png') }}" alt="Forbes Academy Logo">
            </div>

        </div>


        {{-- =========================================
        OVERVIEW
        ========================================= --}}
        <div class="section-header">

            <h2>Overview</h2>

            <form action="{{ route('admin.dashboard') }}" method="GET" class="dashboard-filters">

                <select name="school_year" aria-label="Select School Year" onchange="this.form.submit()">
                    @foreach ($schoolYears as $year)
                        <option value="{{ $year }}" {{ $schoolYear == $year ? 'selected' : '' }}>
                            {{ str_replace('-', ' - ', $year) }}
                        </option>
                    @endforeach
                </select>

                <select name="term" aria-label="Select Academic Term" onchange="this.form.submit()">
                    @foreach ($termOptions as $termNumber => $termLabel)
                        <option value="{{ $termNumber }}" {{ $term == $termNumber ? 'selected' : '' }}>
                            {{ $termLabel }}
                        </option>
                    @endforeach
                </select>

            </form>

        </div>


        {{-- =========================================
        STATISTICS
        ========================================= --}}
        <div class="stats-grid">

            {{-- TOTAL STUDENTS --}}
            <a class="stat-card" href="{{ route('admin.students.index') }}" aria-label="Open Student Management">

                <div class="stat-icon student">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>

                <div class="stat-info">
                    <span>Total Students</span>
                    <h3>{{ number_format($totalStudents) }}</h3>

                    <small>
                        <i class="fa-solid fa-users"></i>
                        Grades 7 - 10
                    </small>
                </div>

            </a>


            {{-- TOTAL FACULTIES --}}
            <a class="stat-card" href="{{ route('admin.faculty-management') }}" aria-label="Open Faculty Management">

                <div class="stat-icon teacher">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>

                <div class="stat-info">
                    <span>Total Faculties</span>
                    <h3>{{ number_format($totalFaculties) }}</h3>

                    <small>
                        <i class="fa-solid fa-user-check"></i>
                        Active Faculties
                    </small>
                </div>

            </a>


            {{-- TOTAL SUBJECTS --}}
            <div class="stat-card">

                <div class="stat-icon subject">
                    <i class="fa-solid fa-book"></i>
                </div>

                <div class="stat-info">
                    <span>Total Subjects</span>
                    <h3>{{ number_format($totalSubjects) }}</h3>

                    <small>
                        <i class="fa-solid fa-book-open"></i>
                        Active Subjects
                    </small>
                </div>

            </div>


            {{-- GRADE LEVELS --}}
            <div class="stat-card">

                <div class="stat-icon class">
                    <i class="fa-solid fa-layer-group"></i>
                </div>

                <div class="stat-info">
                    <span>Grade Levels</span>
                    <h3>{{ number_format($totalGradeLevels) }}</h3>

                    <small>
                        <i class="fa-solid fa-graduation-cap"></i>
                        Grades 7 - 10
                    </small>
                </div>

            </div>

        </div>


        {{--MAIN DASHBOARD CONTENT--}}
        <div class="content-grid">


            {{--LEFT COLUMN--}}

            {{-- STUDENTS BY GRADE LEVEL --}}
            <div class="content-card">

                <div class="card-header">
                    <div>
                        <h2>Students by Grade Level</h2>

                        <p>
                            Student distribution for
                            {{ str_replace('-', ' - ', $schoolYear) }}
                        </p>
                    </div>
                </div>

                <div class="grade-list">

                    @foreach ($gradeLevelLabels as $gradeLevel)

                        @php
                            $count = (int) ($gradeLevelCounts[$gradeLevel] ?? 0);
                            $width = ($count / $maxGradeCount) * 100;

                            $gradeColors = [
                                'Grade 7' => '#036f35',
                                'Grade 8' => '#16834a',
                                'Grade 9' => '#43a66b',
                                'Grade 10' => '#83c69b',
                            ];

                            $barColor = $gradeColors[$gradeLevel] ?? '#036f35';
                        @endphp

                        <div class="grade-item">

                            <div class="grade-info">
                                <span>{{ $gradeLevel }}</span>

                                <strong>
                                    {{ number_format($count) }} students
                                </strong>
                            </div>

                            <div class="grade-bar">
                                <div class="grade-progress" style="width: {{ $width }}%; background-color: {{ $barColor }};">
                                </div>
                            </div>

                        </div>

                    @endforeach

                </div>

            </div>



            {{-- RECENT USER ACTIVITIES --}}
            <div class="content-card recent-activities-card">

                <div class="card-header">
                    <div>
                        <h2>Recent User Activities</h2>
                        <p>Latest activities in the system</p>
                    </div>
                </div>

                <div class="activity-list">

                    @forelse ($recentActivities as $activity)

                        @php
                            $action = strtolower($activity->action ?? '');

                            $icon = match ($action) {
                                'create' => 'fa-user-plus',
                                'update' => 'fa-user-pen',
                                'delete' => 'fa-trash',
                                'restore' => 'fa-rotate-left',
                                'status_change' => 'fa-toggle-on',
                                'approve' => 'fa-circle-check',
                                'reject', 'returned' => 'fa-circle-xmark',
                                'submit' => 'fa-file-circle-check',
                                'csv_import' => 'fa-file-import',
                                'csv_export' => 'fa-file-export',
                                'password_reset' => 'fa-key',
                                default => 'fa-clock'
                            };

                            $details = $activity->details ?? [];

                            $recordType = \Illuminate\Support\Str::headline(
                                $activity->record_type ?? 'Record'
                            );

                            $actionLabel = \Illuminate\Support\Str::headline($action);

                            $description = match ($action) {
                                'csv_import' =>
                                    'Imported ' . ($details['created'] ?? 0) .
                                    ' new and ' . ($details['updated'] ?? 0) .
                                    ' updated records.',

                                'csv_export' =>
                                    'Exported ' . ($details['rows'] ?? 0) .
                                    ' ' . strtolower($recordType) . ' records.',

                                'password_reset' =>
                                    'Reset a user account password.',

                                'status_change' =>
                                    'Changed the status of a ' . strtolower($recordType) . '.',

                                default =>
                                    $details['description']
                                    ?? $details['message']
                                    ?? ($actionLabel . ' ' . strtolower($recordType) . '.')
                            };
                        @endphp

                        <div class="activity-item">

                            <div class="activity-icon">
                                <i class="fa-solid {{ $icon }}"></i>
                            </div>

                            <div class="activity-info">
                                <h3>{{ $actionLabel }} {{ $recordType }}</h3>

                                <p>{{ $description }}</p>

                                <span>
                                    {{ $activity->actor_name }}
                                    · {{ $activity->created_at->diffForHumans() }}
                                </span>
                            </div>

                        </div>

                    @empty

                        <div class="activity-empty">
                            <i class="fa-regular fa-clock"></i>
                            <h3>No Recent Activities</h3>
                            <p>System activities will appear here once users start performing actions.</p>
                        </div>

                    @endforelse

                </div>

            </div>



            {{-- =====================================
            RIGHT COLUMN
            ====================================== --}}
            <div class="dashboard-side-column">


                {{-- GRADE APPROVAL --}}
                <div class="content-card">

                    <div class="card-header">

                        <div>
                            <h2>Grade Approval</h2>

                            <p>
                                {{ $termOptions[$term] }} submission status
                            </p>
                        </div>

                        <a href="{{ route('admin.grade-approval', [
        'school_year' => $schoolYear,
        'term' => $term
    ]) }}" class="view-btn">
                            View Details
                        </a>

                    </div>

                    <div class="approval-list">

                        {{-- PENDING --}}
                        <div class="approval-item">

                            <div class="approval-icon pending">
                                <i class="fa-solid fa-clock"></i>
                            </div>

                            <div class="approval-info">
                                <span>Pending</span>
                                <strong>{{ number_format($pendingGrades) }}</strong>
                            </div>

                        </div>


                        {{-- APPROVED --}}
                        <div class="approval-item">

                            <div class="approval-icon approved">
                                <i class="fa-solid fa-check"></i>
                            </div>

                            <div class="approval-info">
                                <span>Approved</span>
                                <strong>{{ number_format($approvedGrades) }}</strong>
                            </div>

                        </div>


                        {{-- RETURNED --}}
                        <div class="approval-item">

                            <div class="approval-icon returned">
                                <i class="fa-solid fa-rotate-left"></i>
                            </div>

                            <div class="approval-info">
                                <span>Returned</span>
                                <strong>{{ number_format($returnedGrades) }}</strong>
                            </div>

                        </div>

                    </div>

                </div>


                {{-- QUICK ACTIONS --}}
                <div class="content-card">

                    <div class="card-header">
                        <div>
                            <h2>Quick Actions</h2>
                            <p>Frequently used admin shortcuts</p>
                        </div>
                    </div>

                    <div class="quick-actions">

                        {{-- STUDENT MANAGEMENT --}}
                        <a href="{{ route('admin.students.index') }}" class="quick-action-btn">
                            <i class="fa-solid fa-user-plus"></i>
                            <span>Student Management</span>
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>


                        {{-- FACULTY MANAGEMENT --}}
                        <a href="{{ route('admin.faculty-management') }}" class="quick-action-btn">
                            <i class="fa-solid fa-users"></i>
                            <span>Faculty Management</span>
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>


                        {{-- GRADE APPROVAL --}}
                        <a href="{{ route('admin.grade-approval', [
        'school_year' => $schoolYear,
        'term' => $term
    ]) }}" class="quick-action-btn">
                            <i class="fa-solid fa-clipboard-check"></i>
                            <span>Grade Approval</span>
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>


                        {{-- VIEW ANNOUNCEMENTS --}}
                        <button type="button" class="quick-action-btn"
                            onclick="document.getElementById('viewAllAnnouncementsBtn').click()">

                            <i class="fa-solid fa-bullhorn"></i>
                            <span>View Announcements</span>
                            <i class="fa-solid fa-chevron-right"></i>

                        </button>

                    </div>

                </div>

            </div>


            {{-- =========================================
            GRADE SUBMISSION PROGRESS
            ========================================= --}}
            <div class="content-card grade-submission-card">

                <div class="card-header">
                    <div>
                        <h2>Grade Submission Progress</h2>

                        <p>
                            {{ $termOptions[$term] }} submissions by grade level
                        </p>
                    </div>
                </div>

                <div class="grade-list">

                    @foreach ($gradeLevelLabels as $gradeLevel)

                        @php
                            $progress = $gradeProgressByLevel->get($gradeLevel, []);

                            $pending = (int) ($progress['pending'] ?? 0);
                            $approved = (int) ($progress['approved'] ?? 0);
                            $returned = (int) ($progress['returned'] ?? 0);

                            $total = $pending + $approved + $returned;

                            $pendingWidth = $total > 0 ? ($pending / $total) * 100 : 0;
                            $approvedWidth = $total > 0 ? ($approved / $total) * 100 : 0;
                            $returnedWidth = $total > 0 ? ($returned / $total) * 100 : 0;
                        @endphp

                        <div class="grade-item">

                            <div class="grade-info">
                                <span>{{ $gradeLevel }}</span>

                                <strong>
                                    {{ number_format($total) }} submission batches
                                </strong>
                            </div>

                            <div class="grade-bar" style="display: flex; overflow: hidden;">

                                <div style="width: {{ $pendingWidth }}%; background: #f59e0b;" title="Pending: {{ $pending }}">
                                </div>

                                <div style="width: {{ $approvedWidth }}%; background: #16a34a;"
                                    title="Approved: {{ $approved }}">
                                </div>

                                <div style="width: {{ $returnedWidth }}%; background: #dc2626;"
                                    title="Returned: {{ $returned }}">
                                </div>

                            </div>

                            <div class="submission-legend">

                                <small class="pending-label">
                                    <i class="fas fa-clock"></i>
                                    Pending: <strong>{{ $pending }}</strong>
                                </small>

                                <small class="approved-label">
                                    <i class="fas fa-check-circle"></i>
                                    Approved: <strong>{{ $approved }}</strong>
                                </small>

                                <small class="returned-label">
                                    <i class="fas fa-undo-alt"></i>
                                    Returned: <strong>{{ $returned }}</strong>
                                </small>

                            </div>

                            @if ($total === 0)
                                <small class="no-submissions">
                                    No grade submissions yet.
                                </small>
                            @endif

                        </div>

                    @endforeach

                </div>

            </div>


            {{-- =========================================
            ANNOUNCEMENTS
            ========================================= --}}
            <div class="content-card announcements">

                <div class="card-header">

                    <div>
                        <h2>Announcements</h2>
                        <p>Latest school announcements</p>
                    </div>

                    <button class="view-btn" id="viewAllAnnouncementsBtn" type="button">
                        View All
                    </button>

                </div>

                <div class="announcement-list">

                    @forelse ($latestAnnouncements as $announcement)

                                <div class="announcement-item">

                                    <div class="announcement-icon">
                                        <i class="fa-solid fa-bullhorn"></i>
                                    </div>

                                    <div class="announcement-info">

                                        <h3>{{ $announcement->title }}</h3>

                                        <p>{{ $announcement->message }}</p>

                                        <span>
                                            {{ $announcement->created_at
                        ->timezone(config('app.timezone'))
                        ->diffForHumans() }}
                                        </span>

                                    </div>

                                </div>

                    @empty

                        <div class="announcement-empty">
                            <i class="fa-regular fa-bell"></i>
                            <p>No announcements yet.</p>
                        </div>

                    @endforelse

                </div>

            </div>


            {{-- =========================================
            ANNOUNCEMENT MODAL
            ========================================= --}}
            <div class="modal-overlay" id="announcementModal">

                <div class="modal-box">

                    <div class="modal-header">

                        <div>
                            <h2>Announcements</h2>
                            <p>Full list of school announcements</p>
                        </div>

                        <button class="modal-close" id="closeAnnouncementModal" type="button">

                            <i class="fa-solid fa-xmark"></i>

                        </button>

                    </div>

                    <div class="modal-body">

                        <div class="announcement-list">

                            @forelse ($allAnnouncements as $announcement)

                                                <div class="announcement-item">

                                                    <div class="announcement-icon">
                                                        <i class="fa-solid fa-bullhorn"></i>
                                                    </div>

                                                    <div class="announcement-info">

                                                        <h3>{{ $announcement->title }}</h3>

                                                        <p>{{ $announcement->message }}</p>

                                                        <span>
                                                            {{ $announcement->created_at
                                ->timezone(config('app.timezone'))
                                ->format('F j, Y • h:i A') }}
                                                        </span>

                                                    </div>

                                                </div>

                            @empty

                                <div class="announcement-empty">
                                    <i class="fa-regular fa-bell"></i>
                                    <p>No announcements yet.</p>
                                </div>

                            @endforelse

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

@endsection


@section('page-js')
    <script src="{{ asset('js/admin/dashboards.js') }}"></script>
@endsection