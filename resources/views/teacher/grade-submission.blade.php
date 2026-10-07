@extends('layouts.teacher')

@section('title', 'Grade Submission')

@section('header', 'Grade Submission')

@section('header-description', 'Review and submit your final grades.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/teacher/grade-submission.css') }}">
@endsection

@section('content')

    <section class="grade-submission-container">

        {{-- PAGE INTRO --}}
        <div class="submission-intro">

            <div>
                <h2>Grade Submission</h2>
                <p>
                    Check grade completion and submit your final grades for administrator review.
                </p>
            </div>

            <div class="school-info">
                <span>School Year</span>
                <strong>2026-2027</strong>
            </div>

            <div class="school-info">
                <span>Term</span>
                <strong>1st Term</strong>
            </div>

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
                    <strong>3</strong>
                </div>

            </div>


            {{-- SUBMITTED --}}
            <div class="summary-card">

                <div class="summary-icon completed-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>

                <div class="summary-info">
                    <span>Submitted</span>
                    <strong>2</strong>
                </div>

            </div>


            {{-- PENDING --}}
            <div class="summary-card">

                <div class="summary-icon pending-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>

                <div class="summary-info">
                    <span>Pending</span>
                    <strong>1</strong>
                </div>

            </div>


            {{-- TOTAL STUDENTS --}}
            <div class="summary-card">

                <div class="summary-icon students-icon">
                    <i class="fa-solid fa-users"></i>
                </div>

                <div class="summary-info">
                    <span>Total Students</span>
                    <strong>118</strong>
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

                        <option value="all">
                            All Status
                        </option>

                        <option value="pending">
                            Pending
                        </option>

                        <option value="submitted">
                            Submitted
                        </option>

                    </select>

                </div>

            </div>


            {{-- SUBMISSION LIST --}}
            <div class="submission-list">


                {{-- CLASS 1 --}}
                <div class="submission-card" data-status="pending">

                    <div class="submission-card-top">

                        <div class="class-details">

                            <div class="class-icon">
                                <i class="fa-solid fa-book"></i>
                            </div>

                            <div>

                                <h4>
                                    Grade 7 - Section A
                                </h4>

                                <span>
                                    Mathematics
                                </span>

                            </div>

                        </div>


                        <span class="status-badge pending">

                            <i class="fa-solid fa-clock"></i>

                            Ready to Submit

                        </span>

                    </div>


                    {{-- DETAILS --}}
                    <div class="submission-details">

                        <div class="detail-item">

                            <span>
                                Students
                            </span>

                            <strong>
                                42
                            </strong>

                        </div>


                        <div class="detail-item">

                            <span>
                                Grades Completed
                            </span>

                            <strong>
                                42 / 42
                            </strong>

                        </div>


                        <div class="detail-item">

                            <span>
                                Remarks
                            </span>

                            <strong>
                                Completed
                            </strong>

                        </div>

                    </div>


                    {{-- PROGRESS --}}
                    <div class="submission-progress">

                        <div class="progress-label">

                            <span>
                                Grade Completion
                            </span>

                            <strong>
                                100%
                            </strong>

                        </div>

                        <div class="progress-bar">

                            <div class="progress-fill" style="width: 100%;">
                            </div>

                        </div>

                    </div>


                    {{-- ACTIONS --}}
                    <div class="submission-actions">

                        <button type="button" class="btn-secondary review-btn" data-class="Grade 7 - Section A"
                            data-subject="Mathematics">

                            <i class="fa-solid fa-eye"></i>

                            Review Grades

                        </button>


                        <button type="button" class="btn-primary submit-btn" data-class="Grade 7 - Section A"
                            data-subject="Mathematics">

                            <i class="fa-solid fa-paper-plane"></i>

                            Submit Grades

                        </button>

                    </div>

                </div>


                {{-- CLASS 2 --}}
                <div class="submission-card" data-status="submitted">

                    <div class="submission-card-top">

                        <div class="class-details">

                            <div class="class-icon">
                                <i class="fa-solid fa-flask"></i>
                            </div>

                            <div>

                                <h4>
                                    Grade 8 - Section A
                                </h4>

                                <span>
                                    Science
                                </span>

                            </div>

                        </div>


                        <span class="status-badge submitted">

                            <i class="fa-solid fa-circle-check"></i>

                            Submitted

                        </span>

                    </div>


                    <div class="submission-details">

                        <div class="detail-item">

                            <span>
                                Students
                            </span>

                            <strong>
                                38
                            </strong>

                        </div>


                        <div class="detail-item">

                            <span>
                                Grades Completed
                            </span>

                            <strong>
                                38 / 38
                            </strong>

                        </div>


                        <div class="detail-item">

                            <span>
                                Submitted
                            </span>

                            <strong>
                                Oct. 5, 2026
                            </strong>

                        </div>

                    </div>


                    <div class="submission-progress">

                        <div class="progress-label">

                            <span>
                                Grade Completion
                            </span>

                            <strong>
                                100%
                            </strong>

                        </div>

                        <div class="progress-bar">

                            <div class="progress-fill" style="width: 100%;">
                            </div>

                        </div>

                    </div>


                    <div class="submission-actions">

                        <button type="button" class="btn-secondary review-btn" data-class="Grade 8 - Section A"
                            data-subject="Science">

                            <i class="fa-solid fa-eye"></i>

                            View Submission

                        </button>

                    </div>

                </div>


                {{-- CLASS 3 --}}
                <div class="submission-card" data-status="submitted">

                    <div class="submission-card-top">

                        <div class="class-details">

                            <div class="class-icon">
                                <i class="fa-solid fa-language"></i>
                            </div>

                            <div>

                                <h4>
                                    Grade 9 - Section A
                                </h4>

                                <span>
                                    English
                                </span>

                            </div>

                        </div>


                        <span class="status-badge submitted">

                            <i class="fa-solid fa-circle-check"></i>

                            Submitted

                        </span>

                    </div>


                    <div class="submission-details">

                        <div class="detail-item">

                            <span>
                                Students
                            </span>

                            <strong>
                                38
                            </strong>

                        </div>


                        <div class="detail-item">

                            <span>
                                Grades Completed
                            </span>

                            <strong>
                                38 / 38
                            </strong>

                        </div>


                        <div class="detail-item">

                            <span>
                                Submitted
                            </span>

                            <strong>
                                Oct. 4, 2026
                            </strong>

                        </div>

                    </div>


                    <div class="submission-progress">

                        <div class="progress-label">

                            <span>
                                Grade Completion
                            </span>

                            <strong>
                                100%
                            </strong>

                        </div>

                        <div class="progress-bar">

                            <div class="progress-fill" style="width: 100%;">
                            </div>

                        </div>

                    </div>


                    <div class="submission-actions">

                        <button type="button" class="btn-secondary review-btn" data-class="Grade 9 - Section A"
                            data-subject="English">

                            <i class="fa-solid fa-eye"></i>

                            View Submission

                        </button>

                    </div>

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
                    Grade 7 - Section A
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

                <button type="button" class="modal-cancel" id="cancelSubmission">

                    Cancel

                </button>


                <button type="button" class="modal-confirm" id="confirmSubmission">

                    <i class="fa-solid fa-paper-plane"></i>

                    Confirm Submission

                </button>

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
                            Grade 8 - Section A
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