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

        {{-- DASHBOARD STATISTICS --}}
        <div class="adviser-stats">

            <div class="adviser-stat-card">
                <div class="stat-icon green">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <div class="stat-details">
                    <p>Assigned Classes</p>
                    <h3>3</h3>
                    <span>Classes handled</span>
                </div>
            </div>

            <div class="adviser-stat-card">
                <div class="stat-icon blue">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-details">
                    <p>Total Students</p>
                    <h3>118</h3>
                    <span>Across assigned classes</span>
                </div>
            </div>

            <div class="adviser-stat-card">
                <div class="stat-icon orange">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="stat-details">
                    <p>Pending Reviews</p>
                    <h3>2</h3>
                    <span>Awaiting your review</span>
                </div>
            </div>

            <div class="adviser-stat-card">
                <div class="stat-icon purple">
                    <i class="fa-solid fa-paper-plane"></i>
                </div>
                <div class="stat-details">
                    <p>Forwarded to Admin</p>
                    <h3>5</h3>
                    <span>Submitted for approval</span>
                </div>
            </div>

        </div>

        {{-- RECENT GRADE SUBMISSIONS --}}
        <div class="adviser-panel">
            <div class="panel-header">
                <div>
                    <h3>Recent Grade Submissions</h3>
                    <p>Review the latest grade submissions from your teachers.</p>
                </div>

                <a href="{{ url('/adviser/grade-submissions') }}" class="view-all-link">
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
                        <tr>
                            <td>
                                <div class="class-name">Grade 7 - A</div>
                                <span class="subject-name">Mathematics</span>
                            </td>
                            <td>Maria Santos</td>
                            <td>Oct 15, 2026</td>
                            <td>
                                <span class="status-badge pending">Pending Review</span>
                            </td>
                            <td>
                                <a href="{{ url('/adviser/grade-submissions') }}" class="action-link">
                                    Review
                                </a>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="class-name">Grade 8 - A</div>
                                <span class="subject-name">Science</span>
                            </td>
                            <td>Juan Dela Cruz</td>
                            <td>Oct 14, 2026</td>
                            <td>
                                <span class="status-badge forwarded">Forwarded</span>
                            </td>
                            <td>
                                <a href="{{ url('/adviser/grade-submissions') }}" class="action-link">
                                    View
                                </a>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div class="class-name">Grade 9 - A</div>
                                <span class="subject-name">English</span>
                            </td>
                            <td>Anna Reyes</td>
                            <td>Oct 13, 2026</td>
                            <td>
                                <span class="status-badge pending">Pending Review</span>
                            </td>
                            <td>
                                <a href="{{ url('/adviser/grade-submissions') }}" class="action-link">
                                    Review
                                </a>
                            </td>
                        </tr>
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

                <div class="assigned-class-item">
                    <div class="assigned-class-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="assigned-class-info">
                        <h4>Grade 7 - A</h4>
                        <p>42 Students</p>
                    </div>
                    <i class="fa-solid fa-chevron-right class-arrow"></i>
                </div>

                <div class="assigned-class-item">
                    <div class="assigned-class-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="assigned-class-info">
                        <h4>Grade 8 - A</h4>
                        <p>38 Students</p>
                    </div>
                    <i class="fa-solid fa-chevron-right class-arrow"></i>
                </div>

                <div class="assigned-class-item">
                    <div class="assigned-class-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="assigned-class-info">
                        <h4>Grade 9 - A</h4>
                        <p>38 Students</p>
                    </div>
                    <i class="fa-solid fa-chevron-right class-arrow"></i>
                </div>
            </div>

            {{-- QUICK ACTIONS --}}
            <div class="adviser-panel">
                <div class="panel-header">
                    <div>
                        <h3>Quick Actions</h3>
                        <p>Access your frequently used pages.</p>
                    </div>
                </div>

                <a href="{{ url('/adviser/grade-submissions') }}" class="quick-action-item">
                    <div class="quick-action-icon">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h4>Review Grade Submissions</h4>
                        <p>Check grades submitted by teachers.</p>
                    </div>
                    <i class="fa-solid fa-chevron-right quick-arrow"></i>
                </a>

                <a href="{{ url('/adviser/classes') }}" class="quick-action-item">
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