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

                <h2>Welcome back, Teacher!</h2>

                <p>
                    Manage your classes, grades, and academic records
                    from one place.
                </p>

                <div class="welcome-details">

                    <div class="welcome-detail">
                        <i class="fa-solid fa-calendar"></i>

                        <div>
                            <small>School Year</small>
                            <strong>2026 - 2027</strong>
                        </div>
                    </div>

                    <div class="welcome-divider"></div>

                    <div class="welcome-detail">
                        <i class="fa-solid fa-book-open"></i>

                        <div>
                            <small>Term</small>
                            <strong>1st Term</strong>
                        </div>
                    </div>

                </div>

            </div>

            <div class="welcome-icon">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>

        </div>



        {{-- OVERVIEW --}}

        <div class="section-header">

            <h2>Overview</h2>

        </div>


        {{-- STATISTICS --}}
        <div class="stats-grid">

            {{-- MY CLASSES --}}
            <div class="stat-card">

                <div class="stat-icon student">
                    <i class="fa-solid fa-chalkboard"></i>
                </div>

                <div class="stat-info">

                    <span>My Classes</span>

                    <h3>3</h3>

                    <small>
                        Classes assigned
                    </small>

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

                    <h3>90</h3>

                    <small>
                        Across all classes
                    </small>

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

                    <h3>1</h3>

                    <small>
                        Class needs submission
                    </small>

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

                    <h3>2</h3>

                    <small>
                        Classes completed
                    </small>

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
                            <p>Classes currently assigned to you</p>
                        </div>

                    </div>

                    <button class="view-btn">
                        View All
                    </button>

                </div>


                <div class="class-list">

                    {{-- CLASS 1 --}}
                    <div class="class-item">

                        <div class="class-subject-icon math">
                            <i class="fa-solid fa-calculator"></i>
                        </div>

                        <div class="class-info">

                            <h3>Mathematics</h3>

                            <span>Grade 7 - A</span>

                        </div>

                        <div class="class-students">

                            <i class="fa-solid fa-users"></i>

                            <div>
                                <strong>32</strong>
                                <small>Students</small>
                            </div>

                        </div>

                        <i class="fa-solid fa-chevron-right class-arrow"></i>

                    </div>


                    {{-- CLASS 2 --}}
                    <div class="class-item">

                        <div class="class-subject-icon math">
                            <i class="fa-solid fa-calculator"></i>
                        </div>

                        <div class="class-info">

                            <h3>Mathematics</h3>

                            <span>Grade 8 - B</span>

                        </div>

                        <div class="class-students">

                            <i class="fa-solid fa-users"></i>

                            <div>
                                <strong>30</strong>
                                <small>Students</small>
                            </div>

                        </div>

                        <i class="fa-solid fa-chevron-right class-arrow"></i>

                    </div>


                    {{-- CLASS 3 --}}
                    <div class="class-item">

                        <div class="class-subject-icon science">
                            <i class="fa-solid fa-flask"></i>
                        </div>

                        <div class="class-info">

                            <h3>Science</h3>

                            <span>Grade 9 - A</span>

                        </div>

                        <div class="class-students">

                            <i class="fa-solid fa-users"></i>

                            <div>
                                <strong>28</strong>
                                <small>Students</small>
                            </div>

                        </div>

                        <i class="fa-solid fa-chevron-right class-arrow"></i>

                    </div>

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

                    {{-- ENTERING GRADES --}}
                    <button class="quick-action-btn">
                        <i class="fa-solid fa-plus"></i>
                        <span>Enter Grades</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>

                    {{-- GRADE VIEWING --}}
                    <button class="quick-action-btn">
                        <i class="fa-solid fa-file-lines"></i>
                        <span>View Grades</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>

                    {{-- OVERALL STUDENTS --}}
                    <button class="quick-action-btn">
                        <i class="fa-solid fa-users"></i>
                        <span>My Students</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>

                    {{-- GRADE RECORDS FOR TEACHER --}}
                    <button class="quick-action-btn">
                        <i class="fa-solid fa-chart-column"></i>
                        <span>Grade Records</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>

                </div>

            </div>

        </div>



        {{-- GRADE SUBMISSION AND RECENT USER ACTIVITIES --}}

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
                            <p>Check your grade submission status</p>
                        </div>

                    </div>

                    <button class="view-btn">
                        View All
                    </button>

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

                            {{-- COMPLETED --}}
                            <tr>
                                <td>
                                    <div class="class-table-name">

                                        <strong>Grade 7 - A</strong>

                                        <span>Mathematics</span>

                                    </div>
                                </td>

                                <td>
                                    32 / 32
                                </td>

                                <td>
                                    <span class="status completed">
                                        <i class="fa-solid fa-circle-check"></i>
                                        Completed
                                    </span>
                                </td>
                            </tr>


                            {{-- PENDING --}}
                            <tr>
                                <td>
                                    <div class="class-table-name">

                                        <strong>Grade 8 - B</strong>

                                        <span>Mathematics</span>

                                    </div>
                                </td>

                                <td>
                                    25 / 30
                                </td>

                                <td>
                                    <span class="status pending">
                                        <i class="fa-solid fa-clock"></i>
                                        Pending
                                    </span>
                                </td>
                            </tr>


                            {{-- COMPLETED --}}
                            <tr>
                                <td>
                                    <div class="class-table-name">

                                        <strong>Grade 9 - A</strong>

                                        <span>Science</span>

                                    </div>
                                </td>

                                <td>
                                    28 / 28
                                </td>

                                <td>
                                    <span class="status completed">
                                        <i class="fa-solid fa-circle-check"></i>
                                        Completed
                                    </span>
                                </td>

                            </tr>

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
                            <p>Your latest activities in the system</p>
                        </div>

                    </div>

                    <button class="view-btn">
                        View All
                    </button>

                </div>

                <div class="activity-list">

                    {{-- ACTIVITY 1 --}}
                    <div class="activity-item">

                        <div class="activity-icon">
                            <i class="fa-solid fa-pen"></i>
                        </div>

                        <div class="activity-info">

                            <p>
                                Updated grades for
                                <strong>Grade 7 - A Mathematics</strong>
                            </p>

                            <span>Today, 2:35 PM</span>

                        </div>

                    </div>


                    {{-- ACTIVITY 2 --}}
                    <div class="activity-item">

                        <div class="activity-icon">
                            <i class="fa-solid fa-upload"></i>
                        </div>

                        <div class="activity-info">

                            <p>
                                Submitted grades for
                                <strong>Grade 9 - A Science</strong>
                            </p>

                            <span>Today, 1:20 PM</span>

                        </div>

                    </div>


                    {{-- ACTIVITY 3 --}}
                    <div class="activity-item">

                        <div class="activity-icon">
                            <i class="fa-solid fa-eye"></i>
                        </div>

                        <div class="activity-info">

                            <p>
                                Viewed student records for
                                <strong>Grade 8 - B</strong>
                            </p>

                            <span>Yesterday, 4:15 PM</span>

                        </div>

                    </div>


                    {{-- ACTIVITY 4 --}}
                    <div class="activity-item">

                        <div class="activity-icon">
                            <i class="fa-solid fa-file-import"></i>
                        </div>

                        <div class="activity-info">

                            <p>
                                Imported student records
                            </p>

                            <span>Yesterday, 10:32 AM</span>

                        </div>

                    </div>

                </div>


            </div>

        </div>



        {{-- ANNOUNCEMENTS --}}

        <div class="content-card recent-activity-card">


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

                <button class="view-btn" id="viewAllAnnouncementsBtn">
                    View All
                </button>

            </div>



            <div class="announcement-list">

                {{-- ANNOUNCEMENT 1 --}}
                <div class="announcement-item">

                    <div class="announcement-icon">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>

                    <div class="announcement-info">

                        <h3>Grade Submission Deadline</h3>

                        <p>
                            Final grades must be submitted by
                            October 10, 2026.
                        </p>

                        <span>September 25, 2026</span>

                    </div>

                </div>


                {{-- ANNOUNCEMENT 2 --}}
                <div class="announcement-item">

                    <div class="announcement-icon">
                        <i class="fa-solid fa-calendar"></i>
                    </div>

                    <div class="announcement-info">

                        <h3>Quarter 1 Grading Period</h3>

                        <p>
                            Quarter 1 grading is now open.
                        </p>

                        <span>September 20, 2026</span>

                    </div>

                </div>


                {{-- ANNOUNCEMENT 3 --}}
                <div class="announcement-item">

                    <div class="announcement-icon">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>

                    <div class="announcement-info">

                        <h3>System Maintenance</h3>

                        <p>
                            The grading system will undergo
                            scheduled maintenance.
                        </p>

                        <span>September 18, 2026</span>

                    </div>

                </div>

            </div>



        </div>

    </section>

@endsection


@section('page-js')
    <script src="{{ asset('js/dashboards.js') }}"></script>
@endsection