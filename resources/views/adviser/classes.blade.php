{{-- EXTENDS ADVISER LAYOUT --}}
@extends('layouts.adviser')

{{-- PAGE TITLE --}}
@section('title', 'My Classes')

{{-- PAGE HEADER --}}
@section('header', 'My Classes')

{{-- PAGE HEADER DESCRIPTION --}}
@section('header-description', 'View your advisory class and monitor student records and grade submissions.')

{{-- PAGE CSS --}}
@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/adviser/adviser-classes.css') }}">
@endsection

{{-- MAIN CONTENT --}}
@section('content')

    <div class="adviser-classes-page">

        {{-- ADVISORY CLASS PROFILE --}}
        <section class="adviser-class-profile">

            <div class="class-profile-top">

                <div class="class-profile-icon">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>

                <div class="class-profile-info">
                    <span class="class-profile-label">MY ADVISORY CLASS</span>
                    <h2>Grade 7 - Section A</h2>
                    <p>School Year 2026–2027</p>
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
                        <h4>Grade 7</h4>
                    </div>
                </div>

                <div class="class-detail-item">
                    <span class="detail-icon">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </span>

                    <div>
                        <span class="detail-label">Class Adviser</span>
                        <h4>Assigned Adviser</h4>
                    </div>
                </div>

                <div class="class-detail-item">
                    <span class="detail-icon">
                        <i class="fa-solid fa-calendar-days"></i>
                    </span>

                    <div>
                        <span class="detail-label">Academic Term</span>
                        <h4>1st Term</h4>
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

                <h3>42</h3>
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

                <h3>2</h3>
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
                            <th>Date Submitted</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody id="gradeReviewTableBody">

                        {{-- SAMPLE PENDING SUBMISSION --}}
                        <tr class="grade-review-row" data-search="mathematics assigned teacher" data-status="pending">

                            <td>
                                <div class="subject-cell">
                                    <span class="subject-icon">
                                        <i class="fa-solid fa-calculator"></i>
                                    </span>

                                    <div>
                                        <strong>Mathematics</strong>
                                        <small>Grade 7 - Section A</small>
                                    </div>
                                </div>
                            </td>

                            <td>Assigned Teacher</td>

                            <td>October 15, 2026</td>

                            <td>
                                <span class="grade-status status-pending">
                                    <span></span>
                                    Pending Review
                                </span>
                            </td>

                            <td>
                                <a href="{{ url('/adviser/grade-submissions') }}" class="review-action-link">
                                    Review
                                </a>
                            </td>

                        </tr>

                        {{-- SAMPLE REVIEWED SUBMISSION --}}
                        <tr class="grade-review-row" data-search="science assigned teacher" data-status="reviewed">

                            <td>
                                <div class="subject-cell">
                                    <span class="subject-icon science-icon">
                                        <i class="fa-solid fa-flask"></i>
                                    </span>

                                    <div>
                                        <strong>Science</strong>
                                        <small>Grade 7 - Section A</small>
                                    </div>
                                </div>
                            </td>

                            <td>Assigned Teacher</td>

                            <td>October 14, 2026</td>

                            <td>
                                <span class="grade-status status-reviewed">
                                    <span></span>
                                    Reviewed
                                </span>
                            </td>

                            <td>
                                <a href="{{ url('/adviser/grade-submissions') }}" class="review-action-link">
                                    View
                                </a>
                            </td>

                        </tr>

                        {{-- EMPTY SEARCH RESULTS --}}
                        <tr id="gradeReviewEmpty" style="display: none;">
                            <td colspan="5" class="empty-review-message">
                                <i class="fa-solid fa-folder-open"></i>
                                <p>No matching grade submissions found.</p>
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

            <div class="grade-review-footer">
                <span id="gradeReviewCount">Showing 2 submissions</span>
            </div>

        </section>

    </div>

@endsection

{{-- PAGE JAVASCRIPT --}}
@section('page-js')
    <script src="{{ asset('js/adviser/adviser-classes.js') }}"></script>
@endsection