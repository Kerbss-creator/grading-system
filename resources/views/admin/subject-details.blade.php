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
            <a href="{{ route('admin.subjects.index') }}" class="back-link">
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
                        <h1 id="detailSubjectName">
                            {{ $subject->subject_name }}
                        </h1>
                        <p>View subject information and class assignments.</p>
                    </div>
                </div>

                <div>
                    <h2>Subject Information</h2>
                    <p>Basic information about this subject.</p>
                </div>

                <span class="subject-status">
                    {{ ucfirst($subject->status) }}
                </span>

            </div>

            <div class="subject-information-grid">

                <div class="information-item">
                    <span>Subject Name</span>
                    <strong id="infoSubjectName">
                        {{ $subject->subject_name }}
                    </strong>
                </div>

                <div class="information-item">
                    <span>Subject Code</span>
                    <strong id="infoSubjectCode">
                        {{ $subject->subject_code }}
                    </strong>
                </div>

                <div class="information-item">
                    <span>Applicable Grade Levels</span>
                    <strong>
                        @if($subject->gradeLevels->isNotEmpty())
                            {{ $subject->gradeLevels->pluck('grade_level')->map(fn($grade) => 'Grade ' . $grade)->implode(', ') }}
                        @else
                            Not specified
                        @endif
                    </strong>
                </div>

                <div class="information-item">
                    <span>Subject Type</span>
                    <strong id="infoSubjectType">
                        {{ $subject->subject_type }}
                    </strong>
                </div>

            </div>

        </section>


        {{-- Class Assignments --}}
        <section class="details-card">

            <div class="card-heading">
                <div>
                    <h2>Class Assignments</h2>
                    <p>
                        View the teacher and adviser assigned to each grade level.
                    </p>
                </div>
            </div>

            <div class="class-assignment-list">

                @foreach ($gradeAssignments as $assignment)

                    @php
                        $teacher = $assignment['teacher'];

                        $teacherName = $teacher
                            ? trim(implode(' ', array_filter([
                                $teacher->first_name,
                                $teacher->middle_name,
                                $teacher->last_name
                            ])))
                            : null;
                    @endphp

                    <div class="class-assignment" data-grade="{{ $assignment['grade_level'] }}">

                        <div class="grade-icon">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>

                        <div class="grade-information">

                            <h3>{{ $assignment['grade_level'] }}</h3>

                            <p>
                                <strong>Subject Teacher:</strong>
                                {{ $teacherName ?? 'Not Assigned' }}
                            </p>

                            <p>
                                <strong>Adviser:</strong>

                                @if($assignment['advisers']->isNotEmpty())

                                                    {{ $assignment['advisers']->map(function ($adviser) {
                                        return trim(implode(' ', array_filter([
                                            $adviser->first_name,
                                            $adviser->middle_name,
                                            $adviser->last_name
                                        ])));
                                    })->implode(', ') }}

                                @else
                                    Not Assigned
                                @endif

                            </p>

                        </div>

                        <a href="{{ url('/admin/gradebook') }}?code={{ urlencode($subject->subject_code) }}&grade={{ urlencode(str_replace('Grade ', '', $assignment['grade_level'])) }}&school_year={{ urlencode($schoolYear) }}"
                            class="assignment-label">

                            View Gradebook
                        </a>

                        <i class="fa-solid fa-chevron-right assignment-arrow"></i>

                    </div>

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
                    <span id="assignedTeacherCount">
                        {{ $assignedTeacherCount }}
                        {{ $assignedTeacherCount === 1 ? 'Teacher' : 'Teachers' }}
                    </span>
                </span>

            </div>

            <div class="assigned-teacher-list" id="assignedTeacherList">

                @php
                    $assignedTeachers = $gradeAssignments
                        ->pluck('teacher')
                        ->filter()
                        ->unique('id')
                        ->values();
                @endphp

                @forelse ($assignedTeachers as $teacher)

                    @php
                        $teacherName = trim(implode(' ', array_filter([
                            $teacher->first_name,
                            $teacher->middle_name,
                            $teacher->last_name
                        ])));

                        $teacherGrades = $gradeAssignments
                            ->filter(
                                fn($assignment) =>
                                    $assignment['teacher']?->id === $teacher->id
                            )
                            ->pluck('grade_level')
                            ->implode(', ');
                    @endphp

                    <div class="assigned-teacher-item">

                        <div class="teacher-icon">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>

                        <div class="teacher-information">
                            <h3>{{ $teacherName }}</h3>
                            <p>{{ $teacherGrades }}</p>
                        </div>

                    </div>

                @empty

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

                @endforelse

            </div>

        </section>

    </div>

@endsection

@section('page-js')
    <script src="{{ asset('js/admin/subject-details-admin.js') }}"></script>
@endsection