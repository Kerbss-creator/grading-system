@extends('layouts.admin')

@section('title', 'Grade Management')

@section('header', 'Grade Management')

@section('header-description', 'Manage and monitor student final grades and academic records.')

@section('page-css')

<link rel="stylesheet" href="{{ asset('css/grade-management-admin.css') }}">
@endsection

@section('content')

<!-- PAGE HEADER -->

<div class="page-header">


<div class="header-actions">

    <button class="btn btn-secondary" id="importBtn" type="button">
        <i class="fa-solid fa-file-import"></i>
        Import Excel
    </button>

    <button class="btn btn-secondary" id="exportBtn" type="button">
        <i class="fa-solid fa-file-export"></i>
        Export Excel
    </button>

    <button class="btn btn-primary" id="addGradeBtn" type="button">
        <i class="fa-solid fa-plus"></i>
        Add Grade Record
    </button>

</div>


</div>

<!-- SUMMARY CARDS -->

<div class="summary-grid">

                            
<!-- TOTAL STUDENTS -->
<div class="summary-card">

    <div class="summary-icon students-icon">
        <i class="fa-solid fa-user-graduate"></i>
    </div>

    <div class="summary-info">

        <span>Total Students</span>

        <h2 id="totalStudents">8</h2>

    </div>

</div>


<!-- SUBMITTED -->
<div class="summary-card">

    <div class="summary-icon submitted-icon">
        <i class="fa-solid fa-circle-check"></i>
    </div>

    <div class="summary-info">

        <span>Submitted</span>

        <h2 id="submittedGrades">5</h2>

    </div>

</div>


<!-- PENDING -->
<div class="summary-card">

    <div class="summary-icon pending-icon">
        <i class="fa-solid fa-clock"></i>
    </div>

    <div class="summary-info">

        <span>Pending</span>

        <h2 id="pendingGrades">3</h2>

    </div>

</div>


<!-- PASSING RATE -->
<div class="summary-card">

    <div class="summary-icon passing-icon">
        <i class="fa-solid fa-chart-line"></i>
    </div>

    <div class="summary-info">

        <span>Passing Rate</span>

        <h2 id="passingRate">87.5%</h2>

    </div>

</div>


</div>

<!-- FILTER BAR -->

<div class="filter-container">


<div class="filter-group">

    <label for="gradeFilter">
        Grade Level
    </label>

    <select id="gradeFilter">

        <option value="all">
            All Grade Levels
        </option>

        <option value="Grade 7">
            Grade 7
        </option>

        <option value="Grade 8">
            Grade 8
        </option>

        <option value="Grade 9">
            Grade 9
        </option>

        <option value="Grade 10">
            Grade 10
        </option>

    </select>

</div>


<div class="filter-group">

    <label for="sectionFilter">
        Section
    </label>

    <select id="sectionFilter">

        <option value="all">
            All Sections
        </option>

        <option value="Section A">
            Section A
        </option>

        <option value="Section B">
            Section B
        </option>

        <option value="Section C">
            Section C
        </option>

    </select>

</div>


<div class="filter-group">

    <label for="subjectFilter">
        Subject
    </label>

    <select id="subjectFilter">

        <option value="all">
            All Subjects
        </option>

        <option value="Mathematics">
            Mathematics
        </option>

        <option value="Science">
            Science
        </option>

        <option value="English">
            English
        </option>

        <option value="Filipino">
            Filipino
        </option>

    </select>

</div>


<div class="search-container">

    <i class="fa-solid fa-magnifying-glass"></i>

    <input
        type="text"
        id="searchStudent"
        placeholder="Search student name or ID..."
    >

</div>


</div>

<!-- GRADE RECORD SECTION -->

<div class="grade-section">


<div class="section-header">

    <div>

        <h2 id="gradeSectionTitle">
            Student Grade Records
        </h2>

        <p id="recordCount">
            8 student records
        </p>

    </div>

</div>


<!-- GRADE RECORD CARDS -->
<div
    class="grade-record-grid"
    id="gradeRecordGrid">

    <!-- CARD 1 -->
    <div
        class="grade-record-card"
        data-id="GR-001"
        data-grade="Grade 7"
        data-section="Section A"
        data-subject="Mathematics">

        <div class="record-top">

            <div class="student-profile">

                <div class="student-avatar">
                    JD
                </div>

                <div>

                    <h3>Harvy Hicap</h3>

                    <span>
                        Student ID: 2026-001
                    </span>

                </div>

            </div>

            <span class="grade-status submitted">
                Submitted
            </span>

        </div>


        <div class="record-info">

            <div>
                <span>Grade Level</span>
                <strong>Grade 7</strong>
            </div>

            <div>
                <span>Section</span>
                <strong>Section A</strong>
            </div>

            <div>
                <span>Subject</span>
                <strong>Mathematics</strong>
            </div>

        </div>


        <div class="final-grade">

            <div>

                <span>Final Grade</span>

                <h2>92</h2>

            </div>

            <div class="remark-box">

                <span>Remarks</span>

                <strong class="passed">
                    Passed
                </strong>

            </div>

        </div>


        <div class="record-footer">

            <button class="view-btn" type="button">
                <i class="fa-solid fa-eye"></i>
                View
            </button>

            <button class="edit-btn" type="button">
                <i class="fa-solid fa-pen"></i>
                Edit
            </button>

            <button class="delete-btn" type="button">
                <i class="fa-solid fa-trash"></i>
                Delete
            </button>

        </div>

    </div>


    <!-- CARD 2 -->
    <div
        class="grade-record-card"
        data-id="GR-002"
        data-grade="Grade 7"
        data-section="Section A"
        data-subject="Mathematics">

        <div class="record-top">

            <div class="student-profile">

                <div class="student-avatar">
                    MS
                </div>

                <div>

                    <h3>Marsha Lenathea</h3>

                    <span>
                        Student ID: 2026-002
                    </span>

                </div>

            </div>

            <span class="grade-status submitted">
                Submitted
            </span>

        </div>


        <div class="record-info">

            <div>
                <span>Grade Level</span>
                <strong>Grade 7</strong>
            </div>

            <div>
                <span>Section</span>
                <strong>Section A</strong>
            </div>

            <div>
                <span>Subject</span>
                <strong>Mathematics</strong>
            </div>

        </div>


        <div class="final-grade">

            <div>

                <span>Final Grade</span>

                <h2>88</h2>

            </div>

            <div class="remark-box">

                <span>Remarks</span>

                <strong class="passed">
                    Passed
                </strong>

            </div>

        </div>


        <div class="record-footer">

            <button class="view-btn" type="button">
                <i class="fa-solid fa-eye"></i>
                View
            </button>

            <button class="edit-btn" type="button">
                <i class="fa-solid fa-pen"></i>
                Edit
            </button>

            <button class="delete-btn" type="button">
                <i class="fa-solid fa-trash"></i>
                Delete
            </button>

        </div>

    </div>


    <!-- CARD 3 -->
    <div
        class="grade-record-card"
        data-id="GR-003"
        data-grade="Grade 8"
        data-section="Section B"
        data-subject="Science">

        <div class="record-top">

            <div class="student-profile">

                <div class="student-avatar">
                    CR
                </div>

                <div>

                    <h3>Christy Toya</h3>

                    <span>
                        Student ID: 2026-003
                    </span>

                </div>

            </div>

            <span class="grade-status submitted">
                Submitted
            </span>

        </div>


        <div class="record-info">

            <div>
                <span>Grade Level</span>
                <strong>Grade 8</strong>
            </div>

            <div>
                <span>Section</span>
                <strong>Section B</strong>
            </div>

            <div>
                <span>Subject</span>
                <strong>Science</strong>
            </div>

        </div>


        <div class="final-grade">

            <div>

                <span>Final Grade</span>

                <h2>76</h2>

            </div>

            <div class="remark-box">

                <span>Remarks</span>

                <strong class="passed">
                    Passed
                </strong>

            </div>

        </div>


        <div class="record-footer">

            <button class="view-btn" type="button">
                <i class="fa-solid fa-eye"></i>
                View
            </button>

            <button class="edit-btn" type="button">
                <i class="fa-solid fa-pen"></i>
                Edit
            </button>

            <button class="delete-btn" type="button">
                <i class="fa-solid fa-trash"></i>
                Delete
            </button>

        </div>

    </div>


    <!-- CARD 4 -->
    <div
        class="grade-record-card"
        data-id="GR-004"
        data-grade="Grade 9"
        data-section="Section A"
        data-subject="English">

        <div class="record-top">

            <div class="student-profile">

                <div class="student-avatar">
                    AG
                </div>

                <div>

                    <h3>Angela Garcia</h3>

                    <span>
                        Student ID: 2026-004
                    </span>

                </div>

            </div>

            <span class="grade-status pending">
                Pending
            </span>

        </div>


        <div class="record-info">

            <div>
                <span>Grade Level</span>
                <strong>Grade 9</strong>
            </div>

            <div>
                <span>Section</span>
                <strong>Section A</strong>
            </div>

            <div>
                <span>Subject</span>
                <strong>English</strong>
            </div>

        </div>


        <div class="final-grade">

            <div>

                <span>Final Grade</span>

                <h2 class="no-grade">—</h2>

            </div>

            <div class="remark-box">

                <span>Remarks</span>

                <strong class="pending-text">
                    Pending
                </strong>

            </div>

        </div>


        <div class="record-footer">

            <button class="view-btn" type="button">
                <i class="fa-solid fa-eye"></i>
                View
            </button>

            <button class="edit-btn" type="button">
                <i class="fa-solid fa-pen"></i>
                Edit
            </button>

            <button class="delete-btn" type="button">
                <i class="fa-solid fa-trash"></i>
                Delete
            </button>

        </div>

    </div>

</div>


</div>

<!-- ADD / EDIT MODAL -->

<div class="modal" id="gradeModal">
<div class="modal-content">

    <div class="modal-header">

        <div>

            <h2 id="modalTitle">
                Add Grade Record
            </h2>

            <p id="modalDescription">
                Enter student final grade information.
            </p>

        </div>

        <button
            class="modal-close"
            id="closeGradeModal"
            type="button">

            <i class="fa-solid fa-xmark"></i>

        </button>

    </div>


    <div class="form-grid">

        <div class="form-group">

            <label for="studentName">
                Student Name
            </label>

            <input
                type="text"
                id="studentName"
                placeholder="Enter student name">

        </div>


        <div class="form-group">

            <label for="studentId">
                Student ID
            </label>

            <input
                type="text"
                id="studentId"
                placeholder="Enter student ID">

        </div>


        <div class="form-group">

            <label for="gradeLevel">
                Grade Level
            </label>

            <select id="gradeLevel">

                <option value="">
                    Select Grade Level
                </option>

                <option>Grade 7</option>
                <option>Grade 8</option>
                <option>Grade 9</option>
                <option>Grade 10</option>

            </select>

        </div>


        <div class="form-group">

            <label for="section">
                Section
            </label>

            <select id="section">

                <option value="">
                    Select Section
                </option>

                <option>Section A</option>
                <option>Section B</option>
                <option>Section C</option>

            </select>

        </div>


        <div class="form-group">

            <label for="subject">
                Subject
            </label>

            <select id="subject">

                <option value="">
                    Select Subject
                </option>

                <option>Mathematics</option>
                <option>Science</option>
                <option>English</option>
                <option>Filipino</option>

            </select>

        </div>


        <div class="form-group">

            <label for="finalGrade">
                Final Grade
            </label>

            <input
                type="number"
                id="finalGrade"
                min="0"
                max="100"
                placeholder="Enter final grade">

        </div>


        <div class="form-group full">

            <label for="remarks">
                Remarks
            </label>

            <select id="remarks">

                <option value="Passed">
                    Passed
                </option>

                <option value="Failed">
                    Failed
                </option>

                <option value="Pending">
                    Pending
                </option>

            </select>

        </div>

    </div>


    <div class="modal-footer">

        <button
            class="btn btn-secondary"
            id="cancelGrade"
            type="button">

            Cancel

        </button>

        <button
            class="btn btn-primary"
            id="saveGrade"
            type="button">

            <i class="fa-solid fa-check"></i>

            <span id="saveButtonText">
                Save Grade Record
            </span>

        </button>

    </div>

</div>

</div>

<!-- VIEW MODAL -->

<div class="modal" id="viewModal">
<div class="modal-content view-modal">

    <div class="modal-header">

        <div>

            <h2>
                Student Grade Record
            </h2>

            <p>
                Academic grade information
            </p>

        </div>

        <button
            class="modal-close"
            id="closeViewModal"
            type="button">

            <i class="fa-solid fa-xmark"></i>

        </button>

    </div>


    <div class="view-profile">

        <div
            class="large-avatar"
            id="viewAvatar">

            JD

        </div>

        <div>

            <h2 id="viewStudentName">
                Juan Dela Cruz
            </h2>

            <p id="viewStudentId">
                Student ID: 2026-001
            </p>

        </div>

    </div>


    <div class="view-details">

        <div>

            <span>Grade Level</span>

            <strong id="viewGrade">
                Grade 7
            </strong>

        </div>


        <div>

            <span>Section</span>

            <strong id="viewSection">
                Section A
            </strong>

        </div>


        <div>

            <span>Subject</span>

            <strong id="viewSubject">
                Mathematics
            </strong>

        </div>


        <div class="view-final-grade">

            <span>Final Grade</span>

            <strong id="viewFinalGrade">
                92
            </strong>

        </div>


        <div>

            <span>Remarks</span>

            <strong id="viewRemarks">
                Passed
            </strong>

        </div>

    </div>


    <div class="modal-footer">

        <button
            class="btn btn-secondary"
            id="closeViewButton"
            type="button">

            Close

        </button>

    </div>

</div>
</div>

@endsection

@section('page-js')

<script src="{{ asset('js/grade-management-admin.js') }}"></script>

@endsection
