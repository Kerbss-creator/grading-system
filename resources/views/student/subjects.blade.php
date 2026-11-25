@extends('layouts.student')

@section('title', 'My Subjects')

@section('header', 'My Subjects')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/student/student-subjects.css') }}">
@endsection

@section('content')

    <div class="student-subjects-page">

        {{-- PAGE INTRODUCTION --}}
        <div class="student-subjects-intro">
            <div>
                <h2>My Subjects</h2>
                <p>
                    View the subjects and teachers assigned to your class
                    for the current school year.
                </p>
            </div>

            <div class="student-school-year">
                <i class="fa-solid fa-calendar-days"></i>
                <span>{{ $schoolYearLabel }}</span>
            </div>
        </div>

        {{-- STUDENT INFORMATION --}}
        @if ($student)
            <div class="student-subjects-info">

                {{-- STUDENT NAME --}}
                <div class="student-info-item">
                    <small>Student Name</small>
                    <strong>
                        {{ $student->first_name }}
                        {{ $student->middle_name ? $student->middle_name . ' ' : '' }}
                        {{ $student->last_name }}
                    </strong>
                </div>

                {{-- GRADE LEVEL --}}
                <div class="student-info-item">
                    <small>Grade Level</small>
                    <strong>{{ $student->grade_level }}</strong>
                </div>

                {{-- CLASS ADVISER --}}
                <div class="student-info-item">
                    <small>Class Adviser</small>

                    @if ($adviser)
                        <strong>
                            {{ $adviser->first_name }}
                            {{ $adviser->middle_name ? $adviser->middle_name . ' ' : '' }}
                            {{ $adviser->last_name }}
                        </strong>
                    @else
                        <strong class="student-no-adviser">
                            Not Yet Assigned
                        </strong>
                    @endif
                </div>

            </div>
        @endif

        {{-- SUBJECT LIST --}}
        <div class="student-subjects-heading">
            <div>
                <h3>Enrolled Subjects</h3>
                <p>
                    Subjects available for your grade level.
                </p>
            </div>

            <span class="student-subject-count">
                {{ $subjects->count() }} Subjects
            </span>
        </div>

        @if (!$student)

            {{-- STUDENT PROFILE NOT FOUND --}}
            <div class="student-subjects-page-empty">
                <i class="fa-solid fa-user-slash"></i>
                <h3>Student Profile Not Found</h3>
                <p>
                    Your student account is not currently linked
                    to a student profile. Please contact the administrator.
                </p>
            </div>

        @elseif (!$schoolYear)

            {{-- NO ACTIVE SCHOOL YEAR --}}
            <div class="student-subjects-page-empty">
                <i class="fa-solid fa-calendar-xmark"></i>
                <h3>No Active School Year</h3>
                <p>
                    Subject assignments will be available once the
                    administrator activates a school year.
                </p>
            </div>

        @elseif ($subjects->isEmpty())

            {{-- NO SUBJECTS AVAILABLE --}}
            <div class="student-subjects-page-empty">
                <i class="fa-solid fa-book-open"></i>
                <h3>No Subjects Available</h3>
                <p>
                    No subjects are currently assigned to your grade level.
                    Please contact the administrator for assistance.
                </p>
            </div>

        @else

            {{-- SUBJECT CARDS --}}
            <div class="student-subjects-page-grid">

                @foreach ($subjects as $subject)

                    <article class="student-subject-page-card">

                        {{-- SUBJECT HEADER --}}
                        <div class="student-subject-page-header">

                            <div class="student-subject-page-icon">
                                <i class="fa-solid fa-book"></i>
                            </div>

                            <div class="student-subject-page-title">
                                <h4>{{ $subject->subject_name }}</h4>

                                <span>
                                    {{ $subject->subject_code }}
                                </span>
                            </div>

                        </div>

                        {{-- SUBJECT TYPE --}}
                        <div class="student-subject-type">
                            {{ $subject->subject_type }}
                        </div>

                        {{-- ASSIGNED TEACHERS --}}
                        <div class="student-subject-page-teachers">

                            <h5>
                                <i class="fa-solid fa-chalkboard-user"></i>
                                Assigned Teacher{{ $subject->teachers->count() > 1 ? 's' : '' }}
                            </h5>

                            @forelse ($subject->teachers as $teacher)

                                <div class="student-subject-page-teacher">

                                    <div class="student-subject-page-teacher-icon">
                                        <i class="fa-solid fa-user-tie"></i>
                                    </div>

                                    <div class="student-subject-page-teacher-info">

                                        @if (
                                                !empty($teacher->component) &&
                                                strtolower($teacher->component) !== 'general'
                                            )
                                            <small>{{ $teacher->component }}</small>
                                        @else
                                            <small>Subject Teacher</small>
                                        @endif

                                        <strong>
                                            {{ $teacher->first_name }}
                                            {{ $teacher->middle_name ? $teacher->middle_name . ' ' : '' }}
                                            {{ $teacher->last_name }}
                                        </strong>

                                    </div>

                                </div>

                            @empty

                                {{-- NO TEACHER ASSIGNED --}}
                                <div class="student-subject-page-no-teacher">
                                    <i class="fa-solid fa-circle-info"></i>

                                    <div>
                                        <strong>Not Yet Assigned</strong>
                                        <p>
                                            No active subject teacher has
                                            been assigned yet.
                                        </p>
                                    </div>
                                </div>

                            @endforelse

                        </div>

                    </article>

                @endforeach

            </div>

        @endif

    </div>

@endsection