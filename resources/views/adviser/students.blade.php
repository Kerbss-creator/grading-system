{{-- EXTENDS ADVISER LAYOUT --}}
@extends('layouts.adviser')

{{-- PAGE TITLE --}}
@section('title', 'My Students')

{{-- PAGE HEADER --}}
@section('header', 'My Students')

{{-- PAGE HEADER DESCRIPTION --}}
@section('header-description', 'View and manage the students assigned to your advisory class.')

{{-- PAGE CSS --}}
@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/adviser/adviser-students.css') }}">
@endsection

{{-- MAIN CONTENT --}}
@section('content')

    <div class="adviser-students-page">

        {{-- STUDENT SUMMARY --}}
        <div class="students-summary-card">
            <div class="summary-info">
                <span class="summary-label">Total Students</span>
                <h2 id="totalStudents">42</h2>
                <p>Students in your advisory class</p>
            </div>

            <div class="summary-icon">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
        </div>

        {{-- CLASS INFORMATION --}}
        <div class="students-class-info">
            <div class="class-info-left">
                <div class="class-icon">
                    <i class="fa-solid fa-school"></i>
                </div>

                <div>
                    <h3>Grade 7 - Section A</h3>
                    <p>School Year 2026–2027 · 1st Term</p>
                </div>
            </div>

            <span class="class-status">
                <i class="fa-solid fa-circle"></i>
                Active Class
            </span>
        </div>

        {{-- STUDENT LIST --}}
        <div class="students-table-card">

            <div class="students-table-header">
                <div>
                    <h3>Student List</h3>
                    <p>Students assigned to your advisory class</p>
                </div>
            </div>

            {{-- SEARCH AND FILTER --}}
            <div class="students-toolbar">

                <div class="student-search">
                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input type="text" id="studentSearch" placeholder="Search by student name or ID..."
                        aria-label="Search students">
                </div>

                <select id="genderFilter" aria-label="Filter by gender">
                    <option value="">All Students</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>

            </div>

            {{-- STUDENT TABLE --}}
            <div class="students-table-wrapper">
                <table class="students-table">

                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Gender</th>
                            <th>Grade & Section</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody id="studentsTableBody">

                        {{-- SAMPLE STUDENT 1 --}}
                        <tr data-gender="male">
                            <td>FA-2026-001</td>
                            <td>
                                <div class="student-name-cell">
                                    <div class="student-avatar">JD</div>
                                    <span>Juan Dela Cruz</span>
                                </div>
                            </td>
                            <td>Male</td>
                            <td>Grade 7 - A</td>
                            <td>
                                <button type="button" class="view-records-btn" data-student-id="FA-2026-001"
                                    data-student-name="Juan Dela Cruz">
                                    <i class="fa-regular fa-eye"></i>
                                    View Records
                                </button>
                            </td>
                        </tr>

                        {{-- SAMPLE STUDENT 2 --}}
                        <tr data-gender="female">
                            <td>FA-2026-002</td>
                            <td>
                                <div class="student-name-cell">
                                    <div class="student-avatar">MS</div>
                                    <span>Maria Santos</span>
                                </div>
                            </td>
                            <td>Female</td>
                            <td>Grade 7 - A</td>
                            <td>
                                <button type="button" class="view-records-btn" data-student-id="FA-2026-002"
                                    data-student-name="Maria Santos">
                                    <i class="fa-regular fa-eye"></i>
                                    View Records
                                </button>
                            </td>
                        </tr>

                        {{-- SAMPLE STUDENT 3 --}}
                        <tr data-gender="male">
                            <td>FA-2026-003</td>
                            <td>
                                <div class="student-name-cell">
                                    <div class="student-avatar">PR</div>
                                    <span>Pedro Reyes</span>
                                </div>
                            </td>
                            <td>Male</td>
                            <td>Grade 7 - A</td>
                            <td>
                                <button type="button" class="view-records-btn" data-student-id="FA-2026-003"
                                    data-student-name="Pedro Reyes">
                                    <i class="fa-regular fa-eye"></i>
                                    View Records
                                </button>
                            </td>
                        </tr>

                        {{-- SAMPLE STUDENT 4 --}}
                        <tr data-gender="female">
                            <td>FA-2026-004</td>
                            <td>
                                <div class="student-name-cell">
                                    <div class="student-avatar">AG</div>
                                    <span>Ana Garcia</span>
                                </div>
                            </td>
                            <td>Female</td>
                            <td>Grade 7 - A</td>
                            <td>
                                <button type="button" class="view-records-btn" data-student-id="FA-2026-004"
                                    data-student-name="Ana Garcia">
                                    <i class="fa-regular fa-eye"></i>
                                    View Records
                                </button>
                            </td>
                        </tr>

                        {{-- SAMPLE STUDENT 5 --}}
                        <tr data-gender="male">
                            <td>FA-2026-005</td>
                            <td>
                                <div class="student-name-cell">
                                    <div class="student-avatar">CR</div>
                                    <span>Carlos Ramos</span>
                                </div>
                            </td>
                            <td>Male</td>
                            <td>Grade 7 - A</td>
                            <td>
                                <button type="button" class="view-records-btn" data-student-id="FA-2026-005"
                                    data-student-name="Carlos Ramos">
                                    <i class="fa-regular fa-eye"></i>
                                    View Records
                                </button>
                            </td>
                        </tr>

                        {{-- SAMPLE STUDENT 6 --}}
                        <tr data-gender="female">
                            <td>FA-2026-006</td>
                            <td>
                                <div class="student-name-cell">
                                    <div class="student-avatar">LT</div>
                                    <span>Lea Torres</span>
                                </div>
                            </td>
                            <td>Female</td>
                            <td>Grade 7 - A</td>
                            <td>
                                <button type="button" class="view-records-btn" data-student-id="FA-2026-006"
                                    data-student-name="Lea Torres">
                                    <i class="fa-regular fa-eye"></i>
                                    View Records
                                </button>
                            </td>
                        </tr>

                    </tbody>

                </table>

                {{-- NO RESULTS MESSAGE --}}
                <div class="students-empty-state" id="studentsEmpty" hidden>
                    <i class="fa-solid fa-user-slash"></i>
                    <h4>No students found</h4>
                    <p>Try another student name, ID, or filter.</p>
                </div>
            </div>

            <div class="students-table-footer">
                <span id="studentCount">Showing 6 sample students</span>
                <span>Grade 7 - Section A</span>
            </div>

        </div>

    </div>

    {{-- STUDENT RECORDS MODAL --}}
    <div class="student-modal" id="studentRecordsModal" hidden>
        <div class="student-modal-backdrop" data-close-modal></div>

        <div class="student-modal-content" role="dialog" aria-modal="true" aria-labelledby="modalStudentName">
            <div class="student-modal-header">
                <div>
                    <span class="modal-eyebrow">STUDENT ACADEMIC RECORD</span>
                    <h3 id="modalStudentName">Student Name</h3>
                    <p id="modalStudentId">Student ID</p>
                </div>

                <button type="button" class="modal-close-btn" id="closeStudentModal" aria-label="Close student records">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-class-badge">
                <i class="fa-solid fa-school"></i>
                Grade 7 - Section A
            </div>

            <div class="records-note">
                <i class="fa-solid fa-circle-info"></i>
                Sample grades for UI demonstration only.
            </div>

            <div class="records-table-wrapper">
                <table class="records-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Final Grade</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>Mathematics</td>
                            <td>90</td>
                            <td><span class="grade-passed">Passed</span></td>
                        </tr>
                        <tr>
                            <td>Science</td>
                            <td>88</td>
                            <td><span class="grade-passed">Passed</span></td>
                        </tr>
                        <tr>
                            <td>English</td>
                            <td>92</td>
                            <td><span class="grade-passed">Passed</span></td>
                        </tr>
                        <tr>
                            <td>Filipino</td>
                            <td>89</td>
                            <td><span class="grade-passed">Passed</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="student-modal-footer">
                <button type="button" class="modal-done-btn" id="doneStudentModal">
                    Close
                </button>
            </div>
        </div>
    </div>

@endsection

{{-- PAGE JS --}}
@section('page-js')
    <script src="{{ asset('js/adviser/adviser-students.js') }}"></script>
@endsection