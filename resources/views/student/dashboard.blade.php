@extends('layouts.student')

@section('title', 'My Dashboard | Forbes Academy')

@section('header', 'My Dashboard')

@section('header-description', 'Welcome to your student portal!')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/student/student-dashboard.css') }}">
@endsection

@section('content')

    <section class="student-dashboard">

        <div class="student-container">

            {{-- WELCOME CARD --}}
            <div class="welcome-card">

                <div class="welcome-text">

                    <span>FORBES ACADEMY</span>

                    @if ($student)
                        <h2>
                            Welcome {{ $student->first_name }}
                            {{ $student->last_name }}!
                        </h2>

                        <p>
                            View your grades, academic records, and the latest
                            school announcements in one place.
                        </p>
                    @else
                        <h2>Welcome, {{ $user->name }}!</h2>

                        <p>
                            Your student profile is not yet linked to your account.
                            Please contact your school administrator for assistance.
                        </p>
                    @endif

                    <div class="welcome-details">

                        {{-- SCHOOL YEAR --}}
                        <div class="welcome-detail">
                            <i class="fa-solid fa-calendar"></i>

                            <div>
                                <small>School Year</small>
                                <strong>{{ $schoolYearLabel }}</strong>
                            </div>
                        </div>

                        <div class="welcome-divider"></div>

                        {{-- CURRENT TERM --}}
                        <div class="welcome-detail">
                            <i class="fa-solid fa-book-open"></i>

                            <div>
                                <small>Current Term</small>
                                <strong>{{ $currentTermLabel }}</strong>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="welcome-icon">
                    <img src="{{ asset('images/forbesologo.png') }}" alt="Forbes Academy Logo">
                </div>

            </div>


            {{-- MY ACADEMIC INFORMATION --}}
            @if ($student)
                <section class="student-academic-section">

                    <div class="student-academic-header">
                        <div class="student-section-heading">
                            <div class="student-section-icon">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>

                            <div>
                                <h2>My Academic Information</h2>
                                <p>Your registered student information.</p>
                            </div>
                        </div>
                    </div>

                    <div class="student-academic-grid">

                        {{-- STUDENT ID --}}
                        <div class="student-academic-item">
                            <div class="student-academic-item-icon">
                                <i class="fa-solid fa-id-card"></i>
                            </div>

                            <div>
                                <small>Student ID</small>
                                <strong>{{ $student->student_id }}</strong>
                            </div>
                        </div>

                        {{-- GRADE LEVEL --}}
                        <div class="student-academic-item">
                            <div class="student-academic-item-icon">
                                <i class="fa-solid fa-school"></i>
                            </div>

                            <div>
                                <small>Grade Level</small>
                                <strong>{{ $student->grade_level }}</strong>
                            </div>
                        </div>

                        {{-- ADVISER --}}
                        <div class="student-academic-item">
                            <div class="student-academic-item-icon">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>

                            <div>
                                <small>Class Adviser</small>

                                <strong>
                                    @if ($adviser)
                                        {{ $adviser->first_name }}
                                        {{ $adviser->middle_name ? $adviser->middle_name . ' ' : '' }}
                                        {{ $adviser->last_name }}
                                    @else
                                        Not Yet Assigned
                                    @endif
                                </strong>
                            </div>
                        </div>

                    </div>

                </section>


                {{-- SUBJECT TEACHERS PREVIEW --}}
                <section class="student-subjects-section">

                    <div class="student-section-heading">
                        <div class="student-section-icon">
                            <i class="fa-solid fa-book-open"></i>
                        </div>

                        <div>
                            <h2>My Subject Teachers</h2>
                            <p>
                                View the teachers assigned to your subjects
                                for {{ $schoolYearLabel }}.
                            </p>
                        </div>
                    </div>

                    <div class="student-subjects-grid">

                        @forelse ($subjectTeachers as $subject)

                            <article class="student-subject-card">

                                <div class="student-subject-card-header">

                                    <div class="student-subject-icon">
                                        <i class="fa-solid fa-book"></i>
                                    </div>

                                    <div class="student-subject-info">
                                        <h3>{{ $subject->subject_name }}</h3>

                                        @if (!empty($subject->subject_code))
                                            <span>{{ $subject->subject_code }}</span>
                                        @endif
                                    </div>

                                </div>

                                <div class="student-subject-teachers">

                                    @forelse ($subject->teachers as $teacher)

                                        <div class="student-subject-teacher">

                                            <div class="student-teacher-icon">
                                                <i class="fa-solid fa-user-tie"></i>
                                            </div>

                                            <div class="student-teacher-info">

                                                <small>
                                                    @if (
                                                            !empty($teacher->component) &&
                                                            strtolower($teacher->component) !== 'general'
                                                        )
                                                        {{ $teacher->component }} Teacher
                                                    @else
                                                        Subject Teacher
                                                    @endif
                                                </small>

                                                <strong>
                                                    {{ $teacher->first_name }}
                                                    {{ $teacher->middle_name ? $teacher->middle_name . ' ' : '' }}
                                                    {{ $teacher->last_name }}
                                                </strong>

                                            </div>

                                        </div>

                                    @empty

                                        <div class="student-no-teacher">

                                            <i class="fa-solid fa-circle-info"></i>

                                            <div>
                                                <strong>No Subject Teacher Assigned</strong>

                                                <p>
                                                    A subject teacher has not yet been
                                                    assigned to this class.
                                                </p>
                                            </div>

                                        </div>

                                    @endforelse

                                </div>

                            </article>

                        @empty

                            <div class="student-subjects-empty" role="status">

                                <div class="student-empty-icon">
                                    <i class="fa-solid fa-book-open"></i>
                                </div>

                                <h3>No Subjects Available</h3>

                                <p>
                                    No subjects are currently listed for your
                                    grade level. Please contact your school
                                    administrator for assistance.
                                </p>

                            </div>

                        @endforelse

                    </div>

                </section>

            @endif


            {{-- QUICK ACCESS --}}
            <section class="student-quick-access-section">

                <div class="student-section-heading">
                    <div class="student-section-icon">
                        <i class="fa-solid fa-bolt"></i>
                    </div>

                    <div>
                        <h2>Quick Access</h2>
                        <p>Quickly access your student portal features.</p>
                    </div>
                </div>

                <div class="student-quick-access-grid">

                    {{-- MY GRADES --}}
                    <a href="{{ route('student.grades') }}" class="student-quick-access-card">

                        <div class="quick-access-icon grades-icon">
                            <i class="fa-solid fa-chart-column"></i>
                        </div>

                        <div class="quick-access-content">
                            <h3>My Grades</h3>
                            <p>View your academic grades and performance.</p>
                        </div>

                        <i class="fa-solid fa-arrow-right quick-access-arrow"></i>
                    </a>

                    {{-- ANNOUNCEMENTS --}}
                    <a href="{{ route('student.announcements') }}" class="student-quick-access-card">

                        <div class="quick-access-icon announcements-icon">
                            <i class="fa-solid fa-bullhorn"></i>
                        </div>

                        <div class="quick-access-content">
                            <h3>Announcements</h3>
                            <p>Stay updated with the latest school news.</p>
                        </div>

                        <i class="fa-solid fa-arrow-right quick-access-arrow"></i>
                    </a>

                    {{-- MY PROFILE --}}
                    <a href="{{ route('student.profile') }}" class="student-quick-access-card">

                        <div class="quick-access-icon profile-icon">
                            <i class="fa-solid fa-user"></i>
                        </div>

                        <div class="quick-access-content">
                            <h3>My Profile</h3>
                            <p>View your personal student information.</p>
                        </div>

                        <i class="fa-solid fa-arrow-right quick-access-arrow"></i>
                    </a>

                </div>

            </section>


            {{-- STUDENT PROFILE NOTICE --}}
            @if (!$student)
                <div class="student-profile-notice" role="status">
                    <i class="fa-solid fa-circle-info"></i>

                    <div>
                        <strong>Student Profile Not Linked</strong>
                        <p>
                            Your account is active, but no student record is
                            currently associated with it. Please contact your
                            administrator.
                        </p>
                    </div>
                </div>
            @endif


            {{-- ANNOUNCEMENTS SECTION --}}
            <section class="student-announcements-section">

                <div class="student-section-header">

                    <div class="student-section-heading">

                        <div class="student-section-icon">
                            <i class="fa-solid fa-bullhorn"></i>
                        </div>

                        <div>
                            <h2>Announcements</h2>

                            <p>
                                Stay updated with the latest school announcements.
                            </p>
                        </div>

                    </div>

                    <a href="{{ route('student.announcements') }}" class="view-all-announcements">
                        View All
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                </div>


                {{-- ANNOUNCEMENT LIST --}}
                @forelse ($announcements as $announcement)

                    <article class="student-announcement-item">

                        <div class="student-announcement-icon">
                            <i class="fa-solid fa-bullhorn"></i>
                        </div>

                        <div class="student-announcement-content">

                            <h3>{{ $announcement->title }}</h3>

                            <p>
                                {{ $announcement->message }}
                            </p>

                            <small>
                                <i class="fa-regular fa-calendar"></i>
                                {{ $announcement->created_at?->format('F d, Y') ?? 'Date unavailable' }}
                            </small>

                        </div>

                    </article>

                @empty

                    {{-- EMPTY ANNOUNCEMENTS STATE --}}
                    <div class="student-announcement-empty" role="status">

                        <div class="student-empty-icon">
                            <i class="fa-regular fa-bell-slash"></i>
                        </div>

                        <h3>No Announcement Yet</h3>

                        <p>
                            There are no announcements to display at the moment.
                            Please check back later.
                        </p>

                    </div>

                @endforelse

            </section>

        </div>

    </section>

@endsection

@section('page-js')
    <script src="{{ asset('js/student/student-dashboard.js') }}"></script>
@endsection