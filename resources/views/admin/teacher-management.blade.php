@extends('layouts.admin')
@section('title', 'Teacher Management')
@section('header', 'Teacher Management')
@section('header-description', 'Manage teacher records, information, and accounts.')
@section('page-css')
<link rel="stylesheet" href="{{ asset('css/admin/teacher-management-admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/records-data.css') }}">
@endsection

@section('content')
<section class="teacher-management">
    <div class="records-utility-bar" data-record-type="teachers">
        <a class="records-button" href="{{ route('admin.imports.index', 'teachers') }}"><i class="fa-solid fa-file-import"></i> Import CSV</a>
        <a class="records-button" href="{{ route('admin.exports.csv', 'teachers') }}"><i class="fa-solid fa-file-export"></i> Export CSV</a>

        <button class="records-button" type="button" data-bulk-status="active" disabled><i class="fa-solid fa-circle-check"></i> Activate Selected</button>
        <button class="records-button" type="button" data-bulk-status="inactive" disabled><i class="fa-solid fa-circle-pause"></i> Deactivate Selected</button>
        <button class="records-button records-danger" type="button" data-bulk-delete disabled><i class="fa-solid fa-trash-can"></i> Delete Selected (<span data-selected-count>0</span>)</button>
        <a class="records-button" href="{{ route('admin.recycle-bin') }}"><i class="fa-solid fa-rotate-left"></i> Recycle Bin</a>
    </div>

    <div class="teacher-toolbar">
        <button class="add-teacher-btn" type="button" id="addTeacherBtn">
            <i class="fa-solid fa-plus"></i> Add Teacher
        </button>
        <div class="toolbar-right">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="teacherSearch" placeholder="Search teacher...">
            </div>
            <select class="filter-select" id="subjectFilter">
                <option value="all">All Subjects</option>
                @foreach($availableSubjects as $subject)<option value="{{ $subject }}">{{ $subject }}</option>@endforeach
            </select>
            <select class="filter-select" id="statusFilter">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    <div class="table-container">
        <table class="teacher-table">
            <thead>
                <tr>
                    <th><input type="checkbox" class="records-select-all" aria-label="Select all teachers on this page"></th>
                    <th>Teacher ID</th>
                    <th>Teacher Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>School Year</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="teacherTableBody">
                @foreach($teachers as $teacher)
                <tr data-id="{{ $teacher->id }}"
                    data-teacher-id="{{ $teacher->teacher_id }}"
                    data-first-name="{{ $teacher->first_name }}"
                    data-middle-name="{{ $teacher->middle_name }}"
                    data-last-name="{{ $teacher->last_name }}"
                    data-email="{{ $teacher->email }}"
                    data-subject="{{ $teacher->subject }}"
                    data-school-year="{{ $teacher->school_year }}"
                    data-status="{{ $teacher->status }}"
                    style="{{ $teacher->status === 'inactive' ? 'opacity: 0.5;' : '' }}">
                    <td><input type="checkbox" class="record-check" value="{{ $teacher->id }}" aria-label="Select teacher {{ $teacher->teacher_id }}"></td>
                    <td>{{ $teacher->teacher_id }}</td>
                    <td><a class="records-academic-link" href="{{ route('admin.teachers.show', $teacher) }}" title="View teaching assignments">{{ $teacher->first_name }} {{ $teacher->middle_name }} {{ $teacher->last_name }}</a> @if($teacher->is_demo)<span class="records-demo-label">DEMO</span>@endif</td>
                    <td>{{ $teacher->email }}</td>
                    <td>{{ $teacher->subject }}</td>
                    <td>{{ $teacher->school_year }}</td>
                    <td>
                        <span class="status {{ $teacher->status }}">{{ ucfirst($teacher->status) }}</span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="view-btn" type="button" title="View" onclick="viewTeacher({{ $teacher->id }})">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button class="edit-btn" type="button" title="Edit" onclick="editTeacher({{ $teacher->id }})">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="delete-btn" type="button" title="Delete" onclick="deleteTeacher({{ $teacher->id }})">
                                <i class="fa-solid fa-trash"></i>
                            </button>

                            <button class="action-btn {{ $teacher->status === 'active' ? 'reset' : 'edit' }}"
                                    type="button"
                                    title="{{ $teacher->status === 'active' ? 'Hide' : 'Unhide' }}"
                                    onclick="toggleStatus({{ $teacher->id }})"
                                    style="background: #f0f0f0; color: #555;">
                                <i class="fa-solid {{ $teacher->status === 'active' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <button class="page-btn" type="button"><i class="fa-solid fa-chevron-left"></i></button>
        <button class="page-btn active" type="button">1</button>
        <button class="page-btn" type="button">2</button>
        <button class="page-btn" type="button">3</button>
        <button class="page-btn" type="button">4</button>
        <button class="page-btn" type="button">5</button>
        <button class="page-btn" type="button"><i class="fa-solid fa-chevron-right"></i></button>
    </div>
</section>

<div class="modal-overlay" id="teacherModal">
    <div class="teacher-modal">
        <div class="modal-header">
            <div>
                <h2>Add Teacher</h2>
                <p>Create a new teacher record.</p>
            </div>
            <button type="button" class="modal-close" id="closeTeacherModal" title="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="teacherId">Teacher ID</label>
                <input type="text" id="teacherId" placeholder="Enter teacher ID">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="teacherFirstName">First Name</label>
                    <input type="text" id="teacherFirstName" placeholder="Enter first name">
                </div>
                <div class="form-group">
                    <label for="teacherMiddleName">Middle Name</label>
                    <input type="text" id="teacherMiddleName" placeholder="Enter middle name">
                </div>
            </div>
            <div class="form-group">
                <label for="teacherLastName">Last Name</label>
                <input type="text" id="teacherLastName" placeholder="Enter last name">
            </div>
            <div class="form-group">
                <label for="teacherEmail">Email Address</label>

                <input type="text" id="teacherEmail" placeholder="Enter email address">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="teacherSubject">Subject Assigned</label>
                    <select id="teacherSubject">
                        <option value="">Select Subject</option>
                        <option value="Mathematics">Mathematics</option>
                        <option value="Science">Science</option>
                        <option value="English">English</option>
                        <option value="Filipino">Filipino</option>
                        <option value="Araling Panlipunan">Araling Panlipunan</option>
                        <option value="MAPEH">MAPEH</option>
                        <option value="TLE">TLE</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="teacherSchoolYear">School Year</label>
                    <select id="teacherSchoolYear">
                        <option value="">Select School Year</option>
                        <option value="2026-2027">2026-2027</option>
                        <option value="2027-2028">2027-2028</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="teacherStatus">Status</label>
                <select id="teacherStatus">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="cancel-btn" id="cancelTeacherModal">Cancel</button>
            <button type="button" class="save-teacher-btn" id="saveTeacherBtn">
                <i class="fa-solid fa-plus"></i> Add Teacher
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="viewTeacherModal">
    <div class="teacher-modal view-modal">
        <div class="modal-header">
            <div>
                <h2>Teacher Information</h2>
                <p>View teacher record details.</p>
            </div>
            <button class="modal-close" id="closeViewModal" type="button" title="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="student-info">
                <div class="info-group"><span>Teacher ID</span><strong id="viewTeacherId">-</strong></div>
                <div class="info-group"><span>Teacher Name</span><strong id="viewTeacherName">-</strong></div>
                <div class="info-group"><span>Email</span><strong id="viewEmail">-</strong></div>
                <div class="info-group"><span>Subject</span><strong id="viewSubject">-</strong></div>
                <div class="info-group"><span>School Year</span><strong id="viewSchoolYear">-</strong></div>
                <div class="info-group"><span>Status</span><strong id="viewStatus">-</strong></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="cancel-btn" id="closeViewModalBtn">Close</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="editTeacherModal">
    <div class="teacher-modal">
        <div class="modal-header">
            <div>
                <h2>Edit Teacher</h2>
                <p>Update teacher record information.</p>
            </div>
            <button class="modal-close" id="closeEditModal" type="button" title="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="editTeacherId">Teacher ID</label>
                <input type="text" id="editTeacherId">
            </div>
            <div class="form-group">
                <label for="editTeacherName">Teacher Name</label>
                <input type="text" id="editTeacherName">
            </div>
            <div class="form-group">
                <label for="editEmail">Email Address</label>

                <input type="text" id="editEmail">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="editSubject">Subject Assigned</label>
                    <select id="editSubject">
                        <option value="Mathematics">Mathematics</option>
                        <option value="Science">Science</option>
                        <option value="English">English</option>
                        <option value="Filipino">Filipino</option>
                        <option value="Araling Panlipunan">Araling Panlipunan</option>
                        <option value="MAPEH">MAPEH</option>
                        <option value="TLE">TLE</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editSchoolYear">School Year</label>
                    <select id="editSchoolYear">
                        <option value="2026-2027">2026-2027</option>
                        <option value="2027-2028">2027-2028</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="cancel-btn" id="cancelEditModal">Cancel</button>
            <button type="button" class="save-teacher-btn" id="saveEditTeacher">
                <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="deleteTeacherModal">
    <div class="teacher-modal delete-modal">
        <div class="modal-header">
            <div>
                <h2>Delete Teacher</h2>
                <p>Remove teacher record.</p>
            </div>
            <button class="modal-close" id="closeDeleteModal" type="button" title="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="delete-body">
            <div class="delete-icon"><i class="fa-solid fa-trash"></i></div>
            <h3>Delete this teacher?</h3>
            <p>Are you sure you want to delete <strong id="deleteTeacherName">this teacher</strong>? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="cancel-btn" id="cancelDeleteModal">Cancel</button>
            <button type="button" class="delete-confirm-btn" id="confirmDeleteTeacher">
                <i class="fa-solid fa-trash"></i> Delete
            </button>
        </div>
    </div>
</div>
@endsection

@section('page-js')
<script src="{{ asset('js/admin-records.js') }}" defer>
</script>

<script src="{{ asset('js/teacher-management.js') }}">
</script>
<script>

    window.alert = function(message) {
        if (typeof showModal !== 'undefined') {
            let type = 'info';
            const msg = String(message).toLowerCase();

            if (msg.includes('error') || msg.includes('failed') || msg.includes('incorrect') || msg.includes('taken')) {
                type = 'error';
            } else if (msg.includes('success') || msg.includes('added') || msg.includes('updated')) {
                type = 'success';
            } else if (msg.includes('warning') || msg.includes('sure')) {
                type = 'warning';
            }

            showModal(message, type);
        } else {
            console.warn('Custom modal script not loaded');
        }
    };
</script>
@endsection
