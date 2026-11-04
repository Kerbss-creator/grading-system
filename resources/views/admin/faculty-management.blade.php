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
    <div class="records-utility-bar" data-record-type="teachers">
        <a class="records-button" href="{{ route('admin.imports.index', 'teachers') }}">
            <i class="fa-solid fa-file-import"></i> Import CSV
        </a>

        <a class="records-button" href="{{ route('admin.exports.csv', 'teachers') }}">
            <i class="fa-solid fa-file-export"></i> Export CSV
        </a>

        <button class="records-button" type="button" data-bulk-status="active" disabled>
            <i class="fa-solid fa-circle-check"></i> Activate Selected
        </button>

        <button class="records-button" type="button" data-bulk-status="inactive" disabled>
            <i class="fa-solid fa-circle-pause"></i> Deactivate Selected
        </button>

        <button class="records-button records-danger" type="button" data-bulk-delete disabled>
            <i class="fa-solid fa-trash-can"></i>
            Delete Selected (<span data-selected-count>0</span>)
        </button>

        <a class="records-button" href="{{ route('admin.recycle-bin') }}">
            <i class="fa-solid fa-rotate-left"></i> Recycle Bin
        </a>
    </div>

    {{-- TOOLBAR --}}
    <div class="faculty-toolbar">
        <button type="button" class="add-faculty-btn" id="addFacultyBtn">
            <i class="fa-solid fa-plus"></i>
            Add Faculty
        </button>

        <div class="faculty-toolbar-right">

            <div class="faculty-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" id="facultySearch" placeholder="Search faculty...">
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

        <table class="faculty-table">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" class="records-select-all"
                            aria-label="Select all faculty members on this page">
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

            <tbody id="facultyTableBody">
                @forelse($faculty as $facultyMember)
                <tr
                    data-id="{{ $facultyMember->id }}"
                    data-faculty-id="{{ $facultyMember->faculty_id }}"
                    data-first-name="{{ $facultyMember->first_name }}"
                    data-middle-name="{{ $facultyMember->middle_name }}"
                    data-last-name="{{ $facultyMember->last_name }}"
                    data-role="{{ $facultyMember->role }}"
                    data-email="{{ $facultyMember->email }}"
                    data-status="{{ $facultyMember->status }}">

                    <td>
                        <input type="checkbox" class="record-check"
                            value="{{ $facultyMember->id }}">
                    </td>

                    <td>
                        <span class="faculty-id-text">
                            {{ $facultyMember->faculty_id }}
                        </span>
                    </td>

                    <td>
                        <span class="faculty-name-text">
                            {{ $facultyMember->first_name }}
                            {{ $facultyMember->middle_name }}
                            {{ $facultyMember->last_name }}
                        </span>
                    </td>

                    <td>
                        <span class="faculty-role-badge">
                            {{ $facultyMember->role === 'teacher_adviser'
                                    ? 'Teacher + Adviser'
                                    : ucfirst($facultyMember->role) }}
                        </span>
                    </td>

                    <td>{{ $facultyMember->email }}</td>

                    <td>
                        <div class="faculty-assignment-summary">
                            @forelse($facultyMember->teachingAssignments as $assignment)
                            <span>
                                {{ $assignment->grade_level }} -
                                {{ $assignment->subject }}
                            </span><br>
                            @empty
                            @endforelse

                            @forelse($facultyMember->advisoryAssignments as $advisory)
                            <span>
                                Adviser: {{ $advisory->grade_level }}
                            </span><br>
                            @empty
                            @endforelse
                        </div>
                    </td>

                    <td>
                        <span class="faculty-status-badge {{ strtolower($facultyMember->status) }}">
                            {{ ucfirst($facultyMember->status) }}
                        </span>
                    </td>

                    <td>
                        <div class="faculty-actions">

                            <button class="faculty-action-btn view view-btn"
                                type="button"
                                title="View Faculty"
                                onclick="viewTeacher({{ $facultyMember->id }})">
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            <button class="faculty-action-btn edit edit-btn"
                                type="button"
                                title="Edit Faculty"
                                onclick="editFaculty({{ $facultyMember->id }})">
                                <i class="fa-solid fa-pen"></i>
                            </button>

                            <button class="faculty-action-btn delete delete-btn"
                                type="button"
                                title="Delete Faculty"
                                onclick="deleteFaculty({{ $facultyMember->id }})">
                                <i class="fa-solid fa-trash"></i>
                            </button>

                            <button class="faculty-action-btn toggle action-btn"
                                type="button"
                                title="{{ $facultyMember->status === 'active' ? 'Deactivate' : 'Activate' }}"
                                onclick="toggleStatus({{ $facultyMember->id }})">
                                <i class="fa-solid {{ $facultyMember->status === 'active'
                                    ? 'fa-eye-slash'
                                    : 'fa-eye' }}"></i>
                            </button>

                        </div>
                    </td>
                </tr>
                @empty
                {{-- Empty state is handled below --}}
                @endforelse
            </tbody>
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

            <button type="button" class="add-faculty-btn" id="emptyAddFacultyBtn">
                <i class="fa-solid fa-plus"></i>
                Add Faculty
            </button>
        </div>

    </div>
</section>

{{-- ADD / EDIT FACULTY MODAL --}}
<div class="faculty-modal-overlay" id="facultyFormModal" aria-hidden="true">
    <div class="faculty-modal faculty-modal-large"
        role="dialog" aria-modal="true" aria-labelledby="facultyFormTitle">

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

                <h3 class="faculty-section-title">Basic Information</h3>

                <div class="faculty-form-grid">

                    <div class="faculty-form-group">
                        <label for="facultyId">Faculty ID <span>*</span></label>
                        <input type="text" id="facultyId"
                            placeholder="e.g. FAC-001" required>
                    </div>

                    <div class="faculty-form-group">
                        <label for="facultyEmail">Email Address <span>*</span></label>
                        <input type="email" id="facultyEmail"
                            placeholder="name@example.com" required>
                    </div>

                    <div class="faculty-form-group">
                        <label for="facultyFirstName">First Name <span>*</span></label>
                        <input type="text" id="facultyFirstName" required>
                    </div>

                    <div class="faculty-form-group">
                        <label for="facultyMiddleName">Middle Name</label>
                        <input type="text" id="facultyMiddleName"
                            placeholder="Optional">
                    </div>

                    <div class="faculty-form-group">
                        <label for="facultyLastName">Last Name <span>*</span></label>
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

                <h3 class="faculty-section-title">Responsibilities</h3>
                <p class="faculty-help-text">
                    Choose one or both responsibilities.
                </p>

                <div class="faculty-responsibility-options">

                    <label class="faculty-check-option">
                        <input type="checkbox" id="responsibilityTeacher" checked>
                        <span>
                            <strong>Teacher</strong>
                            <small>Assign subjects and grade levels.</small>
                        </span>
                    </label>

                    <label class="faculty-check-option">
                        <input type="checkbox" id="responsibilityAdviser">
                        <span>
                            <strong>Adviser</strong>
                            <small>Assign one advisory grade level.</small>
                        </span>
                    </label>

                </div>

                <div id="teachingAssignmentsSection">
                    <div class="faculty-section-heading">
                        <div>
                            <h3 class="faculty-section-title">Teaching Assignments</h3>
                            <p class="faculty-help-text">
                                Choose a grade level and subject for each assignment.
                            </p>
                        </div>

                        <button type="button" class="faculty-add-row-btn"
                            id="addAssignmentBtn">
                            <i class="fa-solid fa-plus"></i> Add Assignment
                        </button>
                    </div>

                    <div id="teachingAssignmentsList"></div>
                </div>

                <div id="advisoryAssignmentSection" hidden>
                    <h3 class="faculty-section-title">Advisory Assignment</h3>

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

            <div class="faculty-modal-footer">
                <button type="button" class="faculty-btn-cancel" data-close-modal>
                    Cancel
                </button>

                <button type="submit" class="faculty-btn-save" id="saveFacultyBtn">
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
        role="dialog" aria-modal="true" aria-labelledby="viewFacultyName">

        <div class="faculty-modal-header">
            <div>
                <h2>Faculty Details</h2>
                <p>View faculty information and assignments.</p>
            </div>

            <button type="button" class="faculty-modal-close"
                data-close-modal aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="faculty-modal-body" id="viewFacultyContent"></div>

        <div class="faculty-modal-footer">
            <button type="button" class="faculty-btn-cancel" data-close-modal>
                Close
            </button>
        </div>
    </div>
</div>

{{-- DELETE CONFIRMATION MODAL --}}
<div class="faculty-modal-overlay" id="deleteFacultyModal" aria-hidden="true">
    <div class="faculty-modal faculty-delete-modal"
        role="dialog" aria-modal="true" aria-labelledby="deleteFacultyTitle">

        <div class="faculty-delete-icon">
            <i class="fa-solid fa-trash"></i>
        </div>

        <h2 id="deleteFacultyTitle">Delete Faculty?</h2>
        <p id="deleteFacultyMessage">
            Are you sure you want to delete this faculty record?
        </p>

        <div class="faculty-modal-footer">
            <button type="button" class="faculty-btn-cancel" data-close-modal>
                Cancel
            </button>

            <button type="button" class="faculty-btn-delete" id="confirmDeleteFaculty">
                Delete Faculty
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
