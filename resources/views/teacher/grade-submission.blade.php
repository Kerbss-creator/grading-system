@extends('layouts.teacher')

@section('title', 'Grade Submission')

@section('header', 'Grade Submission')

@section('header-description', 'Review and submit your final grades.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/teacher/grade-submission.css') }}">
@endsection

@section('content')

    <section class="grade-submission-container">

        @if (session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- PAGE INTRO --}}
        <div class="school-info">
            <span>School Year</span>
            <strong>{{ $schoolYear }}</strong>
        </div>

        <div class="school-info">
            <span>Term</span>
            <strong>{{ $term }}{{ $term === 1 ? 'st' : ($term === 2 ? 'nd' : 'rd') }} Term</strong>
        </div>


        {{-- TERM NAVIGATION --}}
<div class="term-navigation">

    @foreach ([1 => '1st Term', 2 => '2nd Term', 3 => '3rd Term'] as $termNumber => $termLabel)

        <a
            href="{{ route('teacher.grade-submission', [
                'school_year' => $schoolYear,
                'term' => $termNumber,
            ]) }}"
            class="term-nav-button {{ (int) $term === $termNumber ? 'active' : '' }}"
            @if ((int) $term === $termNumber) aria-current="page" @endif
        >
            {{ $termLabel }}
        </a>

    @endforeach

</div>
        

        {{-- SUBMISSION SUMMARY --}}
        <div class="submission-summary">

            {{-- ASSIGNED CLASSES --}}
            <div class="summary-card">

                <div class="summary-icon classes-icon">
                    <i class="fa-solid fa-chalkboard"></i>
                </div>

                <div class="summary-info">
                    <span>Assigned Classes</span>
                    <strong>{{ $summary['assigned_classes'] }}</strong>
                </div>

            </div>


            {{-- COMPLETED --}}
            <div class="summary-card">

                <div class="summary-icon completed-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>

                <div class="summary-info">
                    <span>Completed</span>
                    <strong>{{ $summary['completed_students'] }}</strong>
                </div>

            </div>


            {{-- INCOMPLETE --}}
            <div class="summary-card">

                <div class="summary-icon pending-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>

                <div class="summary-info">
                    <span>Incomplete</span>
                    <strong>{{ $summary['incomplete_students'] }}</strong>
                </div>

            </div>


            {{-- TOTAL STUDENTS --}}
            <div class="summary-card">

                <div class="summary-icon students-icon">
                    <i class="fa-solid fa-users"></i>
                </div>

                <div class="summary-info">
                    <span>Total Students</span>
                    <strong>{{ $summary['total_students'] }}</strong>
                </div>

            </div>

        </div>


        {{-- SUBMISSION SECTION --}}
        <div class="submission-section">

            <div class="section-header">

                <div>
                    <h3>My Grade Submissions</h3>
                    <p>Review the status of your class grade submissions.</p>
                </div>

                <div class="submission-filter">

                    <select id="statusFilter">
                        <option value="all">All Status</option>
                        <option value="pending">Incomplete</option>
                        <option value="completed">Completed</option>
                    </select>

                </div>

            </div>


            {{-- SUBMISSION LIST --}}
            <div class="submission-list">

                @forelse ($assignments as $assignment)

                            @php
                                $total = $assignment->total_students;
                                $completed = $assignment->completed_students;
                                $incomplete = $assignment->incomplete_students;
                                $percentage = $assignment->completion_percentage;

                                $className = 'Grade ' . $assignment->grade_level;

                                $isComplete = $total > 0 && $completed === $total;
                            @endphp

                            <div class="submission-card" data-status="{{ $isComplete ? 'completed' : 'pending' }}">

                                {{-- CARD HEADER --}}
                                <div class="submission-card-top">

                                    <div class="class-details">

                                        <div class="class-icon">
                                            <i class="fa-solid fa-book"></i>
                                        </div>

                                        <div>
                                            <h4>{{ $className }}</h4>
                                            <span>{{ $assignment->subject }}</span>
                                        </div>

                                    </div>

                                    <span class="status-badge {{ $isComplete ? 'submitted' : 'pending' }}">

                                        @if ($isComplete)
                                            <i class="fa-solid fa-circle-check"></i>
                                            Grades Complete
                                        @else
                                            <i class="fa-solid fa-clock"></i>
                                            Incomplete
                                        @endif

                                    </span>

                                </div>


                                {{-- DETAILS --}}
                                <div class="submission-details">

                                    <div class="detail-item">
                                        <span>Students</span>
                                        <strong>{{ $total }}</strong>
                                    </div>

                                    <div class="detail-item">
                                        <span>Grades Completed</span>
                                        <strong>{{ $completed }} / {{ $total }}</strong>
                                    </div>

                                    <div class="detail-item">
                                        <span>Incomplete</span>
                                        <strong>{{ $incomplete }}</strong>
                                    </div>

                                </div>


                                {{-- PROGRESS --}}
                                <div class="submission-progress">

                                    <div class="progress-label">

                                        <span>Grade Completion</span>

                                        <strong>{{ number_format($percentage, 2) }}%</strong>

                                    </div>

                                    <div class="progress-bar">

                                        <div class="progress-fill" style="width: {{ $percentage }}%;">
                                        </div>

                                    </div>

                                </div>


                                {{-- ACTIONS --}}
                                <div class="submission-actions">

                                    <a href="{{ route('teacher.grade-review', [
                        'assignment' => $assignment->id,
                        'school_year' => $schoolYear,
                        'term' => $term
                    ]) }}" class="btn-secondary review-btn">
                                        <i class="fa-solid fa-eye"></i>
                                        Review Grades
                                    </a>

                                    @if ($isComplete)

                                        <button type="button" class="btn-primary submit-btn" data-assignment-id="{{ $assignment->id }}"
                                            data-school-year="{{ $schoolYear }}" data-term="{{ $term }}" data-class="{{ $className }}"
                                            data-subject="{{ $assignment->subject }}">
                                            <i class="fa-solid fa-paper-plane"></i>
                                            Submit Grades
                                        </button>

                                    @else

                                        <button type="button" class="btn-primary" disabled>
                                            <i class="fa-solid fa-lock"></i>
                                            Complete All Grades First
                                        </button>

                                    @endif

                                </div>

                            </div>

                @empty
                    <div class="submission-empty">
                        <div class="empty-icon">
                            <i class="fa-solid fa-folder-open"></i>
                        </div>

                        <h4>No Grade Submissions Yet</h4>

                        <p>
                            You don't have any assigned classes or subjects yet.
                            Your teaching assignments will appear here once they are assigned.
                        </p>
                    </div>


                @endforelse

                <div class="submission-filter-empty" id="filterEmptyState" style="display: none;">
                    <div class="empty-icon">
                        <i class="fa-solid fa-filter-circle-xmark"></i>
                    </div>

                    <h4>No Matching Submissions</h4>

                    <p>
                        There are no grade submissions matching the selected status.
                        Try selecting another filter.
                    </p>
                </div>

            </div>

        </div>

    </section>


    {{-- =====================================================
    SUBMISSION CONFIRMATION MODAL
    ===================================================== --}}

    <div class="submission-modal" id="submissionModal">

        <div class="modal-overlay"></div>


        <div class="modal-box">

            <button type="button" class="modal-close" id="closeSubmissionModal">

                <i class="fa-solid fa-xmark"></i>

            </button>


            <div class="modal-icon">

                <i class="fa-solid fa-paper-plane"></i>

            </div>


            <h3>
                Submit Final Grades?
            </h3>


            <p>

                You are about to submit the final grades for

                <strong id="modalClassName">
                    Grade 7
                </strong>

                in

                <strong id="modalSubjectName">
                    Mathematics
                </strong>.

            </p>


            <div class="modal-warning">

                <i class="fa-solid fa-circle-info"></i>

                <span>
                    Please make sure all grades and remarks are correct before submitting.
                </span>

            </div>


            <div class="modal-actions">

                <form id="gradeSubmissionForm" method="POST" action="{{ route('teacher.grade-submission.submit') }}">
                    @csrf

                    <input type="hidden" name="assignment_id" id="submissionAssignmentId">
                    <input type="hidden" name="school_year" id="submissionSchoolYear">
                    <input type="hidden" name="term" id="submissionTerm">

                    <div class="modal-actions">

                        <button type="button" class="modal-cancel" id="cancelSubmission">
                            Cancel
                        </button>

                        <button type="submit" class="modal-confirm" id="confirmSubmission">
                            <i class="fa-solid fa-paper-plane"></i>
                            Confirm Submission
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>


    {{-- =====================================================
    VIEW SUBMISSION MODAL
    ===================================================== --}}

    <div class="view-submission-modal" id="viewSubmissionModal">

        <div class="view-modal-overlay"></div>


        <div class="view-modal-box">

            <button type="button" class="view-modal-close" id="closeViewSubmissionModal">

                <i class="fa-solid fa-xmark"></i>

            </button>


            {{-- HEADER --}}
            <div class="view-modal-header">

                <div class="view-modal-icon">

                    <i class="fa-solid fa-file-circle-check"></i>

                </div>

                <div>

                    <h3>
                        Grade Submission
                    </h3>

                    <p>
                        Submission details and status
                    </p>

                </div>

            </div>


            {{-- STATUS --}}
            <div class="view-status-box">

                <div class="view-status-icon">

                    <i class="fa-solid fa-circle-check"></i>

                </div>

                <div>

                    <span>
                        Submission Status
                    </span>

                    <strong>
                        Submitted
                    </strong>

                </div>

            </div>


            {{-- CLASS INFORMATION --}}
            <div class="view-info-section">

                <h4>
                    Class Information
                </h4>


                <div class="view-info-grid">

                    <div class="view-info-item">

                        <span>
                            Class
                        </span>

                        <strong id="viewClassName">
                            Grade 8
                        </strong>

                    </div>


                    <div class="view-info-item">

                        <span>
                            Subject
                        </span>

                        <strong id="viewSubjectName">
                            Science
                        </strong>

                    </div>


                    <div class="view-info-item">

                        <span>
                            School Year
                        </span>

                        <strong>
                            2026-2027
                        </strong>

                    </div>


                    <div class="view-info-item">

                        <span>
                            Term
                        </span>

                        <strong>
                            1st Term
                        </strong>

                    </div>

                </div>

            </div>


            {{-- SUBMISSION DETAILS --}}
            <div class="view-info-section">

                <h4>
                    Submission Details
                </h4>


                <div class="view-info-grid">

                    <div class="view-info-item">

                        <span>
                            Total Students
                        </span>

                        <strong>
                            38
                        </strong>

                    </div>


                    <div class="view-info-item">

                        <span>
                            Grades Completed
                        </span>

                        <strong>
                            38 / 38
                        </strong>

                    </div>


                    <div class="view-info-item">

                        <span>
                            Submitted By
                        </span>

                        <strong>
                            Teacher
                        </strong>

                    </div>


                    <div class="view-info-item">

                        <span>
                            Submitted On
                        </span>

                        <strong>
                            October 5, 2026
                        </strong>

                    </div>

                </div>

            </div>


            {{-- GRADE SUMMARY --}}
            <div class="view-grade-summary">

                <div class="grade-summary-item">

                    <span>
                        Highest Grade
                    </span>

                    <strong>
                        98
                    </strong>

                </div>


                <div class="grade-summary-item">

                    <span>
                        Lowest Grade
                    </span>

                    <strong>
                        75
                    </strong>

                </div>


                <div class="grade-summary-item">

                    <span>
                        Class Average
                    </span>

                    <strong>
                        88.6
                    </strong>

                </div>

            </div>


            {{-- READ ONLY NOTICE --}}
            <div class="view-readonly-notice">

                <i class="fa-solid fa-lock"></i>

                <span>
                    This submission has already been submitted and is currently read-only.
                </span>

            </div>


            {{-- ACTIONS --}}
            <div class="view-modal-actions">

                <button type="button" class="view-close-btn" id="viewCloseButton">

                    Close

                </button>

            </div>

        </div>

    </div>

@endsection


@section('page-js')

    <script src="{{ asset('js/teacher/grade-submission.js') }}"></script>

@endsection