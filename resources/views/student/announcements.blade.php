@extends('layouts.student')

@section('title', 'Announcements')

@section('header', 'Announcements')

@section('header-description', 'Create and manage announcements for teachers and students.')


@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/teacher/teacher-announcements.css') }}">
@endsection

@section('content')

    <div class="announcements-container">

        {{-- ANNOUNCEMENT LIST --}}
        <div class="announcements-section">

            <div class="section-title">
                <div>
                    <h2>Announcement List</h2>
                    <p>Stay updated with the latest announcements from the administration.</p>
                </div>
            </div>


            {{-- ANNOUNCEMENT CARD --}}
            <div class="announcement-card">

                <div class="announcement-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>

                <div class="announcement-content">

                    <div class="announcement-top">

                        <div>
                            <span class="announcement-badge">
                                Teacher
                            </span>

                            <h3>Grade Submission</h3>
                        </div>

                        <span class="announcement-date">
                            October 6, 2026
                        </span>

                    </div>

                    <p>
                        All teachers are reminded to submit the required grades
                        before the scheduled deadline.
                    </p>

                    <div class="announcement-footer">

                        <span>
                            <i class="fa-solid fa-user-shield"></i>
                            Administration
                        </span>

                        <span>
                            <i class="fa-regular fa-clock"></i>
                            10:30 AM
                        </span>

                    </div>

                </div>

            </div>


            {{-- ANNOUNCEMENT CARD --}}
            <div class="announcement-card">

                <div class="announcement-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>

                <div class="announcement-content">

                    <div class="announcement-top">

                        <div>
                            <span class="announcement-badge">
                                Teacher
                            </span>

                            <h3>Quarterly Evaluation</h3>
                        </div>

                        <span class="announcement-date">
                            October 4, 2026
                        </span>

                    </div>

                    <p>
                        Please ensure that all student academic records are
                        updated and properly reviewed for the quarterly evaluation.
                    </p>

                    <div class="announcement-footer">

                        <span>
                            <i class="fa-solid fa-user-shield"></i>
                            Administration
                        </span>

                        <span>
                            <i class="fa-regular fa-clock"></i>
                            9:15 AM
                        </span>

                    </div>

                </div>

            </div>


            {{-- ANNOUNCEMENT CARD --}}
            <div class="announcement-card">

                <div class="announcement-icon">
                    <i class="fa-solid fa-circle-info"></i>
                </div>

                <div class="announcement-content">

                    <div class="announcement-top">

                        <div>
                            <span class="announcement-badge">
                                System
                            </span>

                            <h3>System Update</h3>
                        </div>

                        <span class="announcement-date">
                            October 1, 2026
                        </span>

                    </div>

                    <p>
                        The grading system has received an update to improve
                        performance and provide a better experience for teachers.
                    </p>

                    <div class="announcement-footer">

                        <span>
                            <i class="fa-solid fa-user-shield"></i>
                            Administration
                        </span>

                        <span>
                            <i class="fa-regular fa-clock"></i>
                            8:00 AM
                        </span>

                    </div>

                </div>

            </div>


            {{-- EMPTY STATE --}}
            <div class="empty-announcements" id="emptyAnnouncements">

                <div class="empty-icon">
                    <i class="fa-regular fa-bell"></i>
                </div>

                <h3>No announcements yet</h3>

                <p>
                    There are currently no announcements from the administration.
                </p>

            </div>

        </div>

    </div>

@endsection


@section('page-js')
    <script src="{{ asset('js/teacher/teacher-announcements.js') }}"></script>
@endsection