@extends('layouts.teacher')

@section('title', 'Grade Management')

@section('header', 'Grade Management')

@section('header-description', 'Manage and update your students\' grades.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/teacher/grade-management.css') }}">
@endsection

@section('content')

    {{-- GRADE MANAGEMENT CONTAINER --}}
    <section class="grade-management-container">

        

        {{-- CLASS AND SUBJECT FILTERS --}}
        <div class="selection-card">

            <div class="selection-group">

                <label for="classSelect">Class</label>

                <div class="select-wrapper">

                    <i class="fa-solid fa-chalkboard"></i>

                    <select id="classSelect">

                        <option value="grade7-a">
                            Grade 7 - Section A
                        </option>

                        <option value="grade8-a">
                            Grade 8 - Section A
                        </option>

                        <option value="grade9-a">
                            Grade 9 - Section A
                        </option>

                        <option value="grade10-a">
                            Grade 10 - Section A
                        </option>

                    </select>

                </div>

            </div>


            <div class="selection-group">

                <label for="subjectSelect">Subject</label>

                <div class="select-wrapper">

                    <i class="fa-solid fa-book"></i>

                    <select id="subjectSelect">

                        <option value="mathematics">
                            Mathematics
                        </option>

                        <option value="science">
                            Science
                        </option>

                        <option value="english">
                            English
                        </option>

                        <option value="filipino">
                            Filipino
                        </option>

                        <option value="filipino">
                            Araling Panlipunan
                        </option>

                        <option value="filipino">
                            TLE
                        </option>

                    </select>

                </div>

            </div>


            <div class="selection-group">

                <label for="schoolYearSelect">School Year</label>

                <div class="select-wrapper">

                    <i class="fa-solid fa-calendar-days"></i>

                    <select id="schoolYearSelect">

                        <option value="2026-2027">
                            2026 - 2027
                        </option>

                        <option value="2027-2028">
                            2027 - 2028
                        </option>

                    </select>

                </div>

            </div>


            <div class="selection-group">

                <label for="termSelect">Term</label>

                <div class="select-wrapper">

                    <i class="fa-solid fa-book-open"></i>

                    <select id="termSelect">
                        <option value="1st-term">
                            1st Term
                        </option>

                        <option value="2nd-term">
                            2nd Term
                        </option>

                        <option value="3rd-term">
                            3rd Term
                        </option>
                    </select>
                </div>

            </div>


            <button type="button" class="load-grades-btn" id="loadGradesBtn">
                <i class="fa-solid fa-rotate"></i>
                Load Grades
            </button>

        </div>


        {{-- GRADE SUMMARY --}}
        <div class="grade-summary">

            {{-- TOTAL STUDENTS --}}
            <div class="summary-card">

                <div class="summary-icon students-icon">
                    <i class="fa-solid fa-users"></i>
                </div>

                <div class="summary-content">

                    <span>Total Students</span>

                    <strong id="totalStudents">
                        32
                    </strong>

                    <small>
                        Students in this class
                    </small>

                </div>

            </div>


            {{-- SUBMITTED GRADES --}}
            <div class="summary-card">

                <div class="summary-icon submitted-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>

                <div class="summary-content">

                    <span>Submitted</span>

                    <strong id="submittedGrades">
                        18
                    </strong>

                    <small>
                        56% of total students
                    </small>

                </div>

            </div>


            {{-- PENDING GRADES --}}
            <div class="summary-card">

                <div class="summary-icon pending-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>

                <div class="summary-content">

                    <span>Pending</span>

                    <strong id="pendingGrades">
                        14
                    </strong>

                    <small>
                        44% of total students
                    </small>

                </div>

            </div>


            {{-- AVERAGE GRADE --}}
            <div class="summary-card">

                <div class="summary-icon average-icon">
                    <i class="fa-solid fa-chart-column"></i>
                </div>

                <div class="summary-content">

                    <span>Average Grade</span>

                    <strong id="averageGrade">
                        86.5
                    </strong>

                    <small>
                        Class average
                    </small>

                </div>

            </div>

        </div>


        {{-- MAIN GRADE CONTENT --}}
        <div class="grade-content-grid">

            {{-- STUDENT GRADES --}}
            <div class="grades-card">

                {{-- CARD HEADER --}}
                <div class="grades-card-header">

                    <div>

                        <h3>Student Grades</h3>

                        <p>
                            Enter the final grade and remarks for each student.
                        </p>

                    </div>

                    {{-- GRADE ACTIONS --}}
                    <div class="grade-actions">

                        <button type="button" class="save-btn" id="saveGradesBtn">

                            <i class="fa-solid fa-floppy-disk"></i>

                            Save Changes

                        </button>

                        <button type="button" class="submit-btn" id="submitGradesBtn">

                            <i class="fa-solid fa-paper-plane"></i>

                            Submit Grades

                        </button>

                    </div>

                </div>


                {{-- TABLE CONTROLS --}}
                <div class="table-controls">

                    <div class="search-box">

                        <i class="fa-solid fa-magnifying-glass"></i>

                        <input type="text" id="studentSearch" placeholder="Search student name or ID...">

                    </div>


                    <div class="status-filter">

                        <i class="fa-solid fa-filter"></i>

                        <select id="statusFilter">

                            <option value="all">
                                All Status
                            </option>

                            <option value="submitted">
                                Submitted
                            </option>

                            <option value="pending">
                                Pending
                            </option>

                        </select>

                    </div>

                </div>


                {{-- GRADE TABLE --}}
                <div class="table-wrapper">

                    <table class="grades-table">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Student ID</th>

                                <th>Name</th>

                                <th>Final Grade</th>

                                <th>Remarks</th>

                                <th>Status</th>

                                <th>Action</th>

                            </tr>

                        </thead>

                        <tbody id="gradeTableBody">

                            {{-- STUDENT 1 --}}
                            <tr data-status="submitted">

                                <td>1</td>

                                <td>2026-001</td>

                                <td>
                                    Ma trishia Jacob
                                </td>

                                <td>

                                    <input type="number" class="grade-input" value="92" min="0" max="100">

                                </td>

                                <td>

                                    <select class="remarks-select">

                                        <option selected>
                                            Outstanding
                                        </option>

                                        <option>
                                            Very Satisfactory
                                        </option>

                                        <option>
                                            Satisfactory
                                        </option>

                                        <option>
                                            Fairly Satisfactory
                                        </option>

                                        <option>
                                            Did Not Meet Expectations
                                        </option>

                                    </select>

                                </td>

                                <td>

                                    <span class="status-badge submitted">
                                        Submitted
                                    </span>

                                </td>

                                <td>

                                    <button type="button" class="edit-btn" title="Edit Grade">

                                        <i class="fa-solid fa-pen"></i>

                                    </button>

                                </td>

                            </tr>


                            {{-- STUDENT 2 --}}
                            <tr data-status="submitted">

                                <td>2</td>

                                <td>2026-002</td>

                                <td>
                                    Harvy Hicap
                                </td>

                                <td>
                                    <input type="number" class="grade-input" value="89" min="0" max="100">
                                </td>

                                <td>

                                    <select class="remarks-select">

                                        <option>
                                            Outstanding
                                        </option>

                                        <option selected>
                                            Very Satisfactory
                                        </option>

                                        <option>
                                            Satisfactory
                                        </option>

                                        <option>
                                            Fairly Satisfactory
                                        </option>

                                        <option>
                                            Did Not Meet Expectations
                                        </option>

                                    </select>

                                </td>

                                <td>

                                    <span class="status-badge submitted">
                                        Submitted
                                    </span>

                                </td>

                                <td>

                                    <button type="button" class="edit-btn" title="Edit Grade">

                                        <i class="fa-solid fa-pen"></i>

                                    </button>

                                </td>

                            </tr>


                            {{-- STUDENT 3 --}}
                            <tr data-status="submitted">

                                <td>3</td>

                                <td>2026-003</td>

                                <td>
                                    Mark Reyes
                                </td>

                                <td>

                                    <input type="number" class="grade-input" value="84" min="0" max="100">

                                </td>

                                <td>

                                    <select class="remarks-select">

                                        <option>
                                            Outstanding
                                        </option>

                                        <option>
                                            Very Satisfactory
                                        </option>

                                        <option selected>
                                            Satisfactory
                                        </option>

                                        <option>
                                            Fairly Satisfactory
                                        </option>

                                        <option>
                                            Did Not Meet Expectations
                                        </option>

                                    </select>

                                </td>

                                <td>

                                    <span class="status-badge submitted">
                                        Submitted
                                    </span>

                                </td>

                                <td>

                                    <button type="button" class="edit-btn" title="Edit Grade">

                                        <i class="fa-solid fa-pen"></i>

                                    </button>

                                </td>

                            </tr>


                            {{-- STUDENT 4 --}}
                            <tr data-status="pending">

                                <td>4</td>

                                <td>2026-004</td>

                                <td>
                                    Lisa Cruz
                                </td>

                                <td>

                                    <input type="number" class="grade-input" value="76" min="0" max="100">

                                </td>

                                <td>

                                    <select class="remarks-select">

                                        <option>
                                            Outstanding
                                        </option>

                                        <option>
                                            Very Satisfactory
                                        </option>

                                        <option>
                                            Satisfactory
                                        </option>

                                        <option selected>
                                            Fairly Satisfactory
                                        </option>

                                        <option>
                                            Did Not Meet Expectations
                                        </option>

                                    </select>

                                </td>

                                <td>

                                    <span class="status-badge pending">
                                        Pending
                                    </span>

                                </td>

                                <td>

                                    <button type="button" class="edit-btn" title="Edit Grade">

                                        <i class="fa-solid fa-pen"></i>

                                    </button>

                                </td>

                            </tr>


                            {{-- STUDENT 5 --}}
                            <tr data-status="submitted">

                                <td>5</td>

                                <td>2026-005</td>

                                <td>
                                    Daniel Ramos
                                </td>

                                <td>

                                    <input type="number" class="grade-input" value="93" min="0" max="100">

                                </td>

                                <td>

                                    <select class="remarks-select">

                                        <option selected>
                                            Outstanding
                                        </option>

                                        <option>
                                            Very Satisfactory
                                        </option>

                                        <option>
                                            Satisfactory
                                        </option>

                                        <option>
                                            Fairly Satisfactory
                                        </option>

                                        <option>
                                            Did Not Meet Expectations
                                        </option>

                                    </select>

                                </td>

                                <td>

                                    <span class="status-badge submitted">
                                        Submitted
                                    </span>

                                </td>

                                <td>

                                    <button type="button" class="edit-btn" title="Edit Grade">

                                        <i class="fa-solid fa-pen"></i>

                                    </button>

                                </td>

                            </tr>


                            {{-- STUDENT 6 --}}
                            <tr data-status="pending">

                                <td>6</td>

                                <td>2026-006</td>

                                <td>
                                    Sophia Garcia
                                </td>

                                <td>

                                    <input type="number" class="grade-input" value="88" min="0" max="100">

                                </td>

                                <td>

                                    <select class="remarks-select">

                                        <option>
                                            Outstanding
                                        </option>

                                        <option selected>
                                            Very Satisfactory
                                        </option>

                                        <option>
                                            Satisfactory
                                        </option>

                                        <option>
                                            Fairly Satisfactory
                                        </option>

                                        <option>
                                            Did Not Meet Expectations
                                        </option>

                                    </select>

                                </td>

                                <td>

                                    <span class="status-badge pending">
                                        Pending
                                    </span>

                                </td>

                                <td>

                                    <button type="button" class="edit-btn" title="Edit Grade">

                                        <i class="fa-solid fa-pen"></i>

                                    </button>

                                </td>

                            </tr>


                            {{-- STUDENT 7 --}}
                            <tr data-status="pending">

                                <td>7</td>

                                <td>2026-007</td>

                                <td>
                                    Matthew Santos
                                </td>

                                <td>

                                    <input type="number" class="grade-input" value="80" min="0" max="100">

                                </td>

                                <td>

                                    <select class="remarks-select">

                                        <option>
                                            Outstanding
                                        </option>

                                        <option>
                                            Very Satisfactory
                                        </option>

                                        <option selected>
                                            Satisfactory
                                        </option>

                                        <option>
                                            Fairly Satisfactory
                                        </option>

                                        <option>
                                            Did Not Meet Expectations
                                        </option>

                                    </select>

                                </td>

                                <td>
                                    <span class="status-badge pending">
                                        Pending
                                    </span>
                                </td>

                                <td>

                                    <button type="button" class="edit-btn" title="Edit Grade">

                                        <i class="fa-solid fa-pen"></i>

                                    </button>

                                </td>

                            </tr>


                            {{-- STUDENT 8 --}}
                            <tr data-status="submitted">

                                <td>8</td>

                                <td>2026-008</td>

                                <td>
                                    Ella Fernandez
                                </td>

                                <td>

                                    <input type="number" class="grade-input" value="95" min="0" max="100">

                                </td>

                                <td>

                                    <select class="remarks-select">

                                        <option selected>
                                            Outstanding
                                        </option>

                                        <option>
                                            Very Satisfactory
                                        </option>

                                        <option>
                                            Satisfactory
                                        </option>

                                        <option>
                                            Fairly Satisfactory
                                        </option>

                                        <option>
                                            Did Not Meet Expectations
                                        </option>

                                    </select>

                                </td>

                                <td>

                                    <span class="status-badge submitted">
                                        Submitted
                                    </span>

                                </td>

                                <td>

                                    <button type="button" class="edit-btn" title="Edit Grade">

                                        <i class="fa-solid fa-pen"></i>

                                    </button>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>


            {{-- RIGHT INFORMATION PANEL --}}
            <aside class="grade-sidebar">

                {{-- GRADE LEGEND --}}
                <div class="info-card">

                    <div class="info-card-title">

                        <i class="fa-solid fa-circle-info"></i>

                        <h3>Grade Legend</h3>

                    </div>


                    <div class="legend-list">

                        <div class="legend-item">

                            <span>90 - 100</span>

                            <strong>Outstanding</strong>

                        </div>

                        <div class="legend-item">

                            <span>85 - 89</span>

                            <strong>Very Satisfactory</strong>

                        </div>

                        <div class="legend-item">

                            <span>80 - 84</span>

                            <strong>Satisfactory</strong>

                        </div>

                        <div class="legend-item">

                            <span>75 - 79</span>

                            <strong>Fairly Satisfactory</strong>

                        </div>

                        <div class="legend-item">

                            <span>Below 75</span>

                            <strong>Did Not Meet Expectations</strong>

                        </div>

                    </div>

                </div>


                {{-- GRADE NOTE --}}
                <div class="info-note">

                    <div class="note-icon">

                        <i class="fa-solid fa-lightbulb"></i>

                    </div>

                    <div>

                        <strong>Note</strong>

                        <p>
                            You can only manage the Final Grade and Remarks for this subject.
                        </p>

                    </div>

                </div>


                {{-- SUBMISSION DEADLINE --}}
                <div class="deadline-card">

                    <div class="deadline-icon">

                        <i class="fa-solid fa-calendar-check"></i>

                    </div>

                    <div>

                        <strong>Submission Deadline</strong>

                        <p>
                            October 15, 2026
                        </p>

                        <small>
                            Subject to change
                        </small>

                    </div>

                </div>

            </aside>

        </div>

    </section>

@endsection


@section('page-js')
    <script src="{{ asset('js/teacher/grade-management.js') }}"></script>
@endsection