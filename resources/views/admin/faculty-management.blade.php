@extends('layouts.admin')

@section('title', 'Faculty Management')
@section('header', 'Faculty Management')
@section('header-description', 'Manage teacher and adviser information in one place.')

@section('page-css')
<link rel="stylesheet" href="{{ asset('css/admin/faculty-management.css') }}">
@endsection

@section('content')

<section class="faculty-management">

    {{-- IMPORT / EXPORT / BULK ACTIONS --}}
    <div class="records-utility-bar" data-record-type="faculty">

        <a class="records-button" href="{{ route('admin.imports.index', 'faculty') }}">
            <i class="fa-solid fa-file-import"></i> Import CSV
        </a>

        <a class="records-button" href="{{ route('admin.exports.csv', 'faculty') }}">
            <i class="fa-solid fa-file-export"></i> Export CSV
        </a>

        <button class="records-button" type="button"
            data-bulk-status="active" disabled>
            <i class="fa-solid fa-circle-check"></i> Activate Selected
        </button>

        <button class="records-button" type="button"
            data-bulk-status="inactive" disabled>
            <i class="fa-solid fa-circle-pause"></i> Deactivate Selected
        </button>

        <button class="records-button records-danger" type="button"
            data-bulk-delete disabled>
            <i class="fa-solid fa-trash-can"></i>
            Delete Selected (<span data-selected-count>0</span>)
        </button>

        <a class="records-button" href="{{ route('admin.recycle-bin') }}">
            <i class="fa-solid fa-rotate-left"></i> Recycle Bin
        </a>

    </div>


    {{-- FACULTY RECORDS --}}
    <section class="faculty-records-card">

        {{-- TOOLBAR --}}
        <div class="faculty-toolbar">

            <button type="button" class="add-faculty-btn" id="addFacultyBtn">
                <i class="fa-solid fa-plus"></i>
                Add Faculty
            </button>

            <div class="faculty-toolbar-right">

                <div class="faculty-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" id="facultySearch"
                        placeholder="Search faculty...">
                </div>

                <select id="roleFilter" class="faculty-filter">
                    <option value="">All Roles</option>
                    <option value="Teacher">Teacher</option>
                    <option value="Adviser">Adviser</option>
                    <option value="Teacher + Adviser">Teacher + Adviser</option>
                </select>

                <select id="subjectFilter" class="faculty-filter">
                    <option value="">All Subjects</option>
                    <option>Filipino</option>
                    <option>English</option>
                    <option>Mathematics</option>
                    <option>Science</option>
                    <option>Araling Panlipunan</option>
                    <option>TLE</option>
                    <option>Values Education</option>
                    <option>MAPEH</option>
                    <option>Computer</option>
                </select>

                <select id="statusFilter" class="faculty-filter">
                    <option value="">All Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>

            </div>
        </div>


        {{-- FACULTY TABLE --}}
        <div class="faculty-table-container">

            <table class="faculty-table" id="facultyTable">

                <thead>
                    <tr>
                        <th>
                            <input type="checkbox"
                                class="records-select-all"
                                aria-label="Select all faculty members">
                        </th>
                        <th>Faculty ID</th>
                        <th>Faculty Name</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Assignments</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                {{-- Rows will be rendered by JavaScript --}}
                <tbody id="facultyTableBody"></tbody>

            </table>


            {{-- EMPTY STATE --}}
            <div class="faculty-empty-state" id="facultyEmptyState" hidden>

                <div class="faculty-empty-icon">
                    <i class="fa-solid fa-users"></i>
                </div>

                <h3 id="facultyEmptyTitle">No Faculty Records Yet</h3>

                <p id="facultyEmptyMessage">
                    Add a faculty member to get started.
                </p>

                <button type="button" class="add-faculty-btn"
                    id="emptyAddFacultyBtn">
                    <i class="fa-solid fa-plus"></i>
                    Add Faculty
                </button>

            </div>

        </div>

    </section>

</section>


{{-- ADD / EDIT FACULTY MODAL --}}
<div class="faculty-modal-overlay" id="facultyFormModal" aria-hidden="true">

    <div class="faculty-modal faculty-modal-large"
        role="dialog"
        aria-modal="true"
        aria-labelledby="facultyFormTitle">

        <div class="faculty-modal-header">

            <div>
                <h2 id="facultyFormTitle">Add Faculty</h2>

                <p id="facultyFormDescription">
                    Enter the faculty information and assignments.
                </p>
            </div>

            <button type="button" class="faculty-modal-close"
                data-close-modal aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>


        <form id="facultyForm">

            @csrf

            <div class="faculty-modal-body">

                {{-- BASIC INFORMATION --}}
                <h3 class="faculty-section-title">Basic Information</h3>

                <div class="faculty-form-grid">

                    <div class="faculty-form-group">
                        <label for="facultyId">
                            Faculty ID <span>*</span>
                        </label>

                        <input type="text" id="facultyId"
                            placeholder="e.g. FAC-001" required>
                    </div>


                    <div class="faculty-form-group">
                        <label for="facultyEmail">
                            Email Address <span>*</span>
                        </label>

                        <input type="email" id="facultyEmail"
                            placeholder="name@example.com" required>
                    </div>


                    <div class="faculty-form-group">
                        <label for="facultyFirstName">
                            First Name <span>*</span>
                        </label>

                        <input type="text" id="facultyFirstName" required>
                    </div>


                    <div class="faculty-form-group">
                        <label for="facultyMiddleName">
                            Middle Name
                        </label>

                        <input type="text" id="facultyMiddleName"
                            placeholder="Optional">
                    </div>


                    <div class="faculty-form-group">
                        <label for="facultyLastName">
                            Last Name <span>*</span>
                        </label>

                        <input type="text" id="facultyLastName" required>
                    </div>


                    <div class="faculty-form-group">
                        <label for="facultyStatus">Status</label>

                        <select id="facultyStatus">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>

                </div>


                {{-- RESPONSIBILITIES --}}
                <h3 class="faculty-section-title">Responsibilities</h3>

                <p class="faculty-help-text">
                    Choose one or both responsibilities.
                </p>

                <div class="faculty-responsibility-options">

                    <label class="faculty-check-option">

                        <input type="checkbox"
                            id="responsibilityTeacher" checked>

                        <span>
                            <strong>Teacher</strong>
                            <small>Assign subjects and grade levels.</small>
                        </span>

                    </label>


                    <label class="faculty-check-option">

                        <input type="checkbox"
                            id="responsibilityAdviser">

                        <span>
                            <strong>Adviser</strong>
                            <small>Assign one advisory grade level.</small>
                        </span>

                    </label>

                </div>


                {{-- TEACHING ASSIGNMENTS --}}
                <div id="teachingAssignmentsSection">

                    <div class="faculty-section-heading">

                        <div>
                            <h3 class="faculty-section-title">
                                Teaching Assignments
                            </h3>

                            <p class="faculty-help-text">
                                Choose a grade level and subject for each assignment.
                            </p>
                        </div>

                        <button type="button"
                            class="faculty-add-row-btn"
                            id="addAssignmentBtn">

                            <i class="fa-solid fa-plus"></i>
                            Add Assignment

                        </button>

                    </div>

                    <div id="teachingAssignmentsList"></div>

                </div>


                {{-- ADVISORY ASSIGNMENT --}}
                <div id="advisoryAssignmentSection" hidden>

                    <h3 class="faculty-section-title">
                        Advisory Assignment
                    </h3>

                    <div class="faculty-form-group">

                        <label for="advisoryGrade">
                            Advisory Grade Level <span>*</span>
                        </label>

                        <select id="advisoryGrade">

                            <option value="">Select Grade Level</option>
                            <option value="Grade 7">Grade 7</option>
                            <option value="Grade 8">Grade 8</option>
                            <option value="Grade 9">Grade 9</option>
                            <option value="Grade 10">Grade 10</option>

                        </select>

                    </div>

                </div>

            </div>


            {{-- FORM FOOTER --}}
            <div class="faculty-modal-footer">

                <button type="button"
                    class="faculty-btn-cancel"
                    data-close-modal>
                    Cancel
                </button>

                <button type="submit"
                    class="faculty-btn-save"
                    id="saveFacultyBtn">

                    <i class="fa-solid fa-check"></i>
                    <span id="saveFacultyText">Save Faculty</span>

                </button>

            </div>

        </form>

    </div>

</div>


{{-- VIEW FACULTY MODAL --}}
<div class="faculty-modal-overlay" id="viewFacultyModal" aria-hidden="true">

    <div class="faculty-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="viewFacultyName">

        <div class="faculty-modal-header">

            <div>
                <h2>Faculty Details</h2>
                <p>View faculty information and assignments.</p>
            </div>

            <button type="button"
                class="faculty-modal-close"
                data-close-modal
                aria-label="Close">

                <i class="fa-solid fa-xmark"></i>

            </button>

        </div>

        <div class="faculty-modal-body" id="viewFacultyContent"></div>

        <div class="faculty-modal-footer">

            <button type="button"
                class="faculty-btn-cancel"
                data-close-modal>
                Close
            </button>

        </div>

    </div>

</div>


{{-- DELETE CONFIRMATION MODAL --}}
<div class="faculty-modal-overlay" id="deleteFacultyModal" aria-hidden="true">

    <div class="faculty-modal faculty-delete-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="deleteFacultyTitle">

        <div class="faculty-delete-icon">
            <i class="fa-solid fa-trash"></i>
        </div>

        <h2 id="deleteFacultyTitle">Delete Faculty?</h2>

        <p id="deleteFacultyMessage">
            Are you sure you want to delete this faculty record?
        </p>

        <div class="faculty-modal-footer">

            <button type="button"
                class="faculty-btn-cancel"
                data-close-modal>
                Cancel
            </button>

            <button type="button"
                class="faculty-btn-delete"
                id="confirmDeleteFaculty">
                Delete Faculty
            </button>

        </div>

    </div>

</div>


{{-- DEACTIVATE / REACTIVATE CONFIRMATION MODAL --}}
<div class="faculty-modal-overlay" id="facultyStatusModal" aria-hidden="true">

    <div class="faculty-modal faculty-delete-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="facultyStatusTitle">

        <div class="faculty-delete-icon" id="facultyStatusIcon">
            <i class="fa-solid fa-user-slash"></i>
        </div>

        <h2 id="facultyStatusTitle">Deactivate Faculty?</h2>

        <p id="facultyStatusMessage">
            Are you sure you want to deactivate this faculty member?
        </p>

        <div class="faculty-modal-footer">

            <button type="button"
                class="faculty-btn-cancel"
                data-close-modal>
                Cancel
            </button>

            <button type="button"
                class="faculty-btn-delete"
                id="confirmFacultyStatus">
                Deactivate Faculty
            </button>

        </div>

    </div>

</div>


{{-- CUSTOM ALERT MODAL --}}
<div class="faculty-modal-overlay" id="facultyAlertModal" aria-hidden="true">

    <div class="faculty-modal faculty-alert-modal"
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="facultyAlertTitle">

        <div class="faculty-alert-icon" id="facultyAlertIcon">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        <h2 id="facultyAlertTitle">Notice</h2>

        <p id="facultyAlertMessage">
            Please check the information and try again.
        </p>

        <div class="faculty-modal-footer">

            <button type="button"
                class="faculty-btn-save"
                data-close-modal>
                Okay, Got it
            </button>

        </div>

    </div>

</div>

@endsection


@section('page-js')

<script>
    window.facultyData = {{ Illuminate\Support\Js::from(
        $faculty->map(function ($item) {

            $advisory = $item->advisoryAssignments->first();

            return [
                'id' => $item->id,
                'facultyId' => $item->faculty_id,
                'firstName' => $item->first_name,
                'middleName' => $item->middle_name ?? '',
                'lastName' => $item->last_name,
                'email' => $item->email,
                'status' => ucfirst($item->status),

                'teacher' => $item->teachingAssignments->isNotEmpty(),
                'adviser' => $item->advisoryAssignments->isNotEmpty(),

                'advisoryGrade' => $advisory->grade_level ?? '',
                'schoolYear' => $advisory->school_year ?? '',

                'assignments' => $item->teachingAssignments->map(function ($assignment) {
                    return [
                        'grade' => $assignment->grade_level,
                        'subject' => $assignment->subject,
                    ];
                })->values(),
            ];

        })->values()
    ) }};
</script>

<script src="{{ asset('js/admin/faculty-management.js') }}" defer></script>

@endsection
