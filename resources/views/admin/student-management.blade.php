@extends('layouts.admin')

@section('title', 'Student Management')
@section('header', 'Student Management')
@section('header-description', 'Manage student records, information, and accounts.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/studentmanagement.css') }}">
@endsection

@section('content')

    <section class="student-management">

        {{-- TOOLBAR --}}
        <div class="student-toolbar">

            <button class="add-student-btn" type="button" id="addStudentBtn">
                <i class="fa-solid fa-plus"></i>
                Add Student
            </button>

            <div class="toolbar-right">

                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="studentSearch" placeholder="Search student...">
                </div>

                <select class="filter-select" id="gradeFilter">
                    <option value="all">All Grades</option>
                    <option value="Grade 7">Grade 7</option>
                    <option value="Grade 8">Grade 8</option>
                    <option value="Grade 9">Grade 9</option>
                    <option value="Grade 10">Grade 10</option>
                </select>

                <select class="filter-select" id="sexFilter">
                    <option value="all">All Sex</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>

                <select class="filter-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>

            </div>
        </div>

        {{-- STUDENT TABLE --}}
        <div class="table-container">
            <table class="student-table">

                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Grade Level</th>
                        <th>Sex</th>
                        <th>School Year</th>
                        <th>Gmail</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody id="studentTableBody">

                    @forelse($students as $student)

                        <tr data-id="{{ $student->id }}" data-student-id="{{ $student->student_id }}"
                            data-first-name="{{ $student->first_name }}" data-middle-name="{{ $student->middle_name }}"
                            data-last-name="{{ $student->last_name }}" data-grade-level="{{ $student->grade_level }}"
                            data-sex="{{ $student->sex }}" data-school-year="{{ $student->school_year }}"
                            data-email="{{ $student->email }}" data-status="{{ $student->status }}">

                            <td>
                                <span class="student-id-text">
                                    {{ $student->student_id }}
                                </span>
                            </td>

                            <td>
                                {{ $student->first_name }}
                                {{ $student->middle_name }}
                                {{ $student->last_name }}
                            </td>

                            <td>{{ $student->grade_level }}</td>
                            <td>{{ $student->sex }}</td>
                            <td>{{ $student->school_year }}</td>
                            <td>{{ $student->email ?: '—' }}</td>

                            <td>
                                <span class="student-status {{ $student->status }}">
                                    {{ ucfirst($student->status) }}
                                </span>
                            </td>

                            <td>
                                <div class="action-buttons">

                                    <button class="view-btn" type="button" title="View Student"
                                        onclick="viewStudent({{ $student->id }})">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    <button class="edit-btn" type="button" title="Edit Student"
                                        onclick="editStudent({{ $student->id }})">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>

                                    @if($student->status === 'active')

                                        <button class="delete-btn" type="button" title="Deactivate Student"
                                            onclick="deleteStudent({{ $student->id }})">
                                            <i class="fa-solid fa-user-slash"></i>
                                        </button>

                                    @else

                                        <button class="activate-btn" type="button" title="Activate Student"
                                            onclick="toggleStatus({{ $student->id }})">
                                            <i class="fa-solid fa-user-check"></i>
                                        </button>

                                    @endif

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr class="empty-state-row">
                            <td colspan="8">
                                <div class="student-empty-state">
                                    <div class="empty-state-icon">
                                        <i class="fa-solid fa-graduation-cap"></i>
                                    </div>

                                    <h3>No Student Records Yet</h3>

                                    <p>
                                        There are no student records to display.
                                        Add your first student to get started.
                                    </p>

                                    <button type="button" class="empty-state-add-btn"
                                        onclick="document.getElementById('addStudentBtn').click()">
                                        <i class="fa-solid fa-plus"></i>
                                        Add Student
                                    </button>
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>

            {{-- JAVASCRIPT EMPTY STATE --}}
            <div id="studentNoResults" class="student-empty-state" hidden>
                <div class="empty-state-icon">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>

                <h3>No Matching Students</h3>
                <p>No student records match your current search or filters.</p>

                <button type="button" id="clearStudentFilters" class="empty-state-add-btn">
                    <i class="fa-solid fa-filter-circle-xmark"></i>
                    Clear Filters
                </button>
            </div>

        </div>

    </section>


    {{-- ADD STUDENT MODAL --}}
    <div class="modal-overlay" id="studentModal">
        <div class="student-modal">

            <div class="modal-header">
                <div>
                    <h2>Add Student</h2>
                    <p>Create a new student record.</p>
                </div>

                <button class="modal-close" id="closeStudentModal" type="button">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body">

                <div class="form-group">
                    <label for="studentId">Student ID</label>
                    <input type="text" id="studentId" placeholder="Enter student ID">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" placeholder="Enter first name">
                    </div>

                    <div class="form-group">
                        <label for="middleName">Middle Name</label>
                        <input type="text" id="middleName" placeholder="Enter middle name">
                    </div>
                </div>

                <div class="form-group">
                    <label for="lastName">Last Name</label>
                    <input type="text" id="lastName" placeholder="Enter last name">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="gradeLevel">Grade Level</label>
                        <select id="gradeLevel">
                            <option value="">Select Grade</option>
                            <option value="Grade 7">Grade 7</option>
                            <option value="Grade 8">Grade 8</option>
                            <option value="Grade 9">Grade 9</option>
                            <option value="Grade 10">Grade 10</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="sex">Sex</label>
                        <select id="sex">
                            <option value="">Select Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="schoolYear">School Year</label>
                    <select id="schoolYear">
                        <option value="">Select School Year</option>
                        <option value="2026-2027">2026-2027</option>
                        <option value="2027-2028">2027-2028</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="email">Gmail / Contact Email</label>
                    <input type="text" id="email" placeholder="Enter student's email (optional)">
                </div>

                <div class="form-group">
                    <label for="studentStatus">Status</label>
                    <select id="studentStatus">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="cancel-btn" id="cancelStudentModal">
                    Cancel
                </button>

                <button type="button" class="save-student-btn" id="saveStudentBtn">
                    <i class="fa-solid fa-plus"></i>
                    Add Student
                </button>
            </div>

        </div>
    </div>


    {{-- VIEW STUDENT MODAL --}}
    <div class="modal-overlay" id="viewStudentModal">
        <div class="student-modal view-modal">

            <div class="modal-header">
                <div>
                    <h2>Student Information</h2>
                    <p>View student record details.</p>
                </div>

                <button class="modal-close" id="closeViewModal" type="button">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="student-info">

                    <div class="info-group">
                        <span>Student ID</span>
                        <strong id="viewStudentId">—</strong>
                    </div>

                    <div class="info-group">
                        <span>Student Name</span>
                        <strong id="viewStudentName">—</strong>
                    </div>

                    <div class="info-group">
                        <span>Grade Level</span>
                        <strong id="viewGradeLevel">—</strong>
                    </div>

                    <div class="info-group">
                        <span>Sex</span>
                        <strong id="viewSex">—</strong>
                    </div>

                    <div class="info-group">
                        <span>School Year</span>
                        <strong id="viewSchoolYear">—</strong>
                    </div>

                    <div class="info-group">
                        <span>Contact Email</span>
                        <strong id="viewGmail">—</strong>
                    </div>

                    <div class="info-group">
                        <span>Account Status</span>
                        <strong id="viewStudentStatus">—</strong>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="cancel-btn" id="closeViewModalBtn">
                    Close
                </button>
            </div>

        </div>
    </div>


    {{-- EDIT STUDENT MODAL --}}
    <div class="modal-overlay" id="editStudentModal">
        <div class="student-modal">

            <div class="modal-header">
                <div>
                    <h2>Edit Student</h2>
                    <p>Update student record information.</p>
                </div>

                <button class="modal-close" id="closeEditModal" type="button">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body">

                <div class="form-group">
                    <label for="editStudentId">Student ID</label>
                    <input type="text" id="editStudentId">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="editFirstName">First Name</label>
                        <input type="text" id="editFirstName">
                    </div>

                    <div class="form-group">
                        <label for="editMiddleName">Middle Name</label>
                        <input type="text" id="editMiddleName">
                    </div>
                </div>

                <div class="form-group">
                    <label for="editLastName">Last Name</label>
                    <input type="text" id="editLastName">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="editGradeLevel">Grade Level</label>
                        <select id="editGradeLevel">
                            <option value="Grade 7">Grade 7</option>
                            <option value="Grade 8">Grade 8</option>
                            <option value="Grade 9">Grade 9</option>
                            <option value="Grade 10">Grade 10</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="editSex">Sex</label>
                        <select id="editSex">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="editSchoolYear">School Year</label>
                    <select id="editSchoolYear">
                        <option value="2026-2027">2026-2027</option>
                        <option value="2027-2028">2027-2028</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="editGmail">Gmail / Contact Email</label>
                    <input type="text" id="editGmail">
                </div>

                <div class="form-group">
                    <label for="editStudentStatus">Status</label>
                    <select id="editStudentStatus">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="cancel-btn" id="cancelEditModal">
                    Cancel
                </button>

                <button type="button" class="save-student-btn" id="saveEditStudent">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save Changes
                </button>
            </div>

        </div>
    </div>


    {{-- DEACTIVATE STUDENT MODAL --}}
    <div class="modal-overlay" id="deleteStudentModal">
        <div class="student-modal delete-modal">

            <div class="modal-header">
                <div>
                    <h2>Deactivate Student</h2>
                    <p>Temporarily disable the student's account.</p>
                </div>

                <button class="modal-close" id="closeDeleteModal" type="button">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="delete-body">

                <div class="delete-icon">
                    <i class="fa-solid fa-user-slash"></i>
                </div>

                <h3>Deactivate this student?</h3>

                <p>
                    Are you sure you want to deactivate
                    <strong id="deleteStudentName">this student</strong>?
                </p>

                <p>
                    The student record, linked account, and grade history will be retained.
                    The student will not be able to log in until the account is activated again.
                </p>

            </div>

            <div class="modal-footer">
                <button type="button" class="cancel-btn" id="cancelDeleteModal">
                    Cancel
                </button>

                <button type="button" class="delete-confirm-btn" id="confirmDeleteStudent">
                    <i class="fa-solid fa-user-slash"></i>
                    Deactivate
                </button>
            </div>

        </div>
    </div>


    {{-- NOTIFICATION MODAL --}}
    <div class="modal-overlay" id="studentNotificationModal">
        <div class="student-modal notification-modal">

            <div class="modal-header">
                <div>
                    <h2 id="notificationTitle">Notification</h2>
                    <p id="notificationSubtitle">Student Management</p>
                </div>

                <button class="modal-close" id="closeNotificationModal" type="button">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="delete-body">
                <div class="delete-icon" id="notificationIcon">
                    <i class="fa-solid fa-circle-info"></i>
                </div>

                <p id="notificationMessage">
                    Notification message will appear here.
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="save-student-btn" id="closeNotificationBtn">
                    Okay
                </button>
            </div>

        </div>
    </div>

@endsection


@section('page-js')
    <script src="{{ asset('js/admin/student-management.js') }}"></script>
@endsection