@extends('layouts.admin')

@section('header', 'Details')

@section('title', 'Subject Details')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/subject-details.css') }}">
@endsection

@section('content')

    <div class="subject-details-page">

        {{-- Page Header --}}
        <div class="details-page-header">

            <a href="{{ url('/admin/subjects') }}" class="back-link">
                <i class="fa-solid fa-arrow-left"></i>
                Back to Subject Management
            </a>

           

        </div>

        {{-- Subject Information --}}
        <section class="details-card">

            <div class="card-heading">

                 <div class="details-heading">
                <div class="subject-icon">
                    <i class="fa-solid fa-book-open"></i>
                </div>

                <div>
                    <h1 id="detailSubjectName">Subject Details</h1>
                    <p>View subject information and class assignments.</p>
                </div>
            </div>

                <div>
                    <h2>Subject Information</h2>
                    <p>Basic information about this subject.</p>
                </div>

                <span class="subject-status">Active</span>
            </div>

            <div class="subject-information-grid">

                <div class="information-item">
                    <span>Subject Name</span>
                    <strong id="infoSubjectName">Mathematics</strong>
                </div>

                <div class="information-item">
                    <span>Subject Code</span>
                    <strong id="infoSubjectCode">MATH</strong>
                </div>

                <div class="information-item">
                    <span>Applicable Grade Levels</span>
                    <strong>Grades 7–10</strong>
                </div>

                <div class="information-item">
                    <span>Subject Type</span>
                    <strong id="infoSubjectType">Core Subject</strong>
                </div>

            </div>

        </section>

        {{-- Class Assignments --}}
        <section class="details-card">

            <div class="card-heading">
                <div>
                    <h2>Class Assignments</h2>
                    <p>
                        Select a grade level to open its class management page.
                    </p>
                </div>
            </div>

            <div class="class-assignment-list">

                @foreach ([7, 8, 9, 10] as $grade)

                    <a href="{{ url('/admin/classes') }}?grade_level={{ $grade }}&subject={{ urlencode(request('subject', 'Mathematics')) }}"
                        class="class-assignment" data-grade="{{ $grade }}">

                        <div class="grade-icon">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>

                        <div class="grade-information">
                            <h3>Grade {{ $grade }}</h3>
                            <p>View class and subject assignments</p>
                        </div>

                        <span class="assignment-label">View Class</span>

                        <i class="fa-solid fa-chevron-right assignment-arrow"></i>

                    </a>

                @endforeach

            </div>

        </section>

        {{-- Assigned Teachers --}}
        <section class="details-card assigned-teachers-section">

            <div class="card-heading">
                <div>
                    <h2>Assigned Teachers</h2>
                    <p>Teachers assigned to handle this subject.</p>
                </div>

                <span class="teacher-count">
                    <i class="fa-solid fa-users"></i>
                    <span id="assignedTeacherCount">0 Teachers</span>
                </span>
            </div>

            <div class="assigned-teacher-list" id="assignedTeacherList">

                {{-- Temporary empty state while the backend is not yet connected --}}
                <div class="teacher-empty-state">
                    <div class="teacher-empty-icon">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>

                    <h3>No Teachers Assigned Yet</h3>
                    <p>
                        Teachers assigned to this subject will appear here
                        once their subject assignments are configured.
                    </p>
                </div>

            </div>

        </section>

    </div>

@endsection

@section('page-js')
    <script src="{{ asset('js/admin/subject-details-admin.js') }}"></script>
@endsection