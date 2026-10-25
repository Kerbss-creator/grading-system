
@extends('layouts.student')

@section('title', 'My Dashboard | Forbes Academy')

@section('header', 'My Dashboard')

@section('header-description', 'Welcome to your student portal!')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/student/student-dashboard.css') }}">
@endsection

@section('content')

    <section class="student-dashboard">

        {{-- DASHBOARD CONTAINER --}}
        <div class="student-container">

            {{-- WELCOME CARD --}}
            <div class="welcome-card">
                <div class="welcome-text">
                    <span>FORBES ACADEMY</span>
                    <h2>Welcome back, {{ $student->first_name }} {{ $student->last_name }}!</h2>
                    <p>
                        View your grades, academic records, and the latest
                        school announcements in one place.
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

                </div>

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

            </section>

        </div>

    </section>

@endsection

@section('page-js')
    <script src="{{ asset('js/student/student-dashboard.js') }}"></script>
@endsection
