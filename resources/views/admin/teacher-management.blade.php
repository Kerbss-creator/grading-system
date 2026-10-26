{{-- @extends('layouts.admin')

@section('title', 'Teacher Management')
@section('header', 'Teacher Management')
@section('header-description', 'Manage teacher records, information, and accounts.')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('css/admin/teacher-management-admin.css') }}">
@endsection

@section('content')

  <section class="teacher-management">

    <div class="teacher-toolbar">
      <button class="add-teacher-btn" type="button" id="addTeacherBtn">
        <i class="fa-solid fa-plus"></i>
        Add Teacher
      </button>

      <div class="toolbar-right">
        <div class="search-box">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" id="teacherSearch" placeholder="Search teacher...">
        </div>

        <select class="filter-select" id="subjectFilter">
          <option value="">All Subjects</option>
          <option value="Mathematics">Mathematics</option>
          <option value="Science">Science</option>
          <option value="English">English</option>
          <option value="Filipino">Filipino</option>
          <option value="Araling Panlipunan">Araling Panlipunan</option>
          <option value="MAPEH">MAPEH</option>
          <option value="TLE">TLE</option>
        </select>

        <select class="filter-select" id="statusFilter">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
    </div>

    <div class="table-container">
      <table class="teacher-table">
        <thead>
          <tr>
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
          @forelse($teachers as $teacher)
            <tr data-id="{{ $teacher->id }}" data-teacher-id="{{ $teacher->teacher_id }}"
              data-first-name="{{ $teacher->first_name }}" data-middle-name="{{ $teacher->middle_name ?? '' }}"
              data-last-name="{{ $teacher->last_name }}" data-email="{{ $teacher->email }}"
              data-subject="{{ $teacher->subject }}" data-school-year="{{ $teacher->school_year }}"
              data-status="{{ strtolower($teacher->status) }}">
              <td>{{ $teacher->teacher_id }}</td>

              <td>
                {{ trim(($teacher->first_name ?? '') . ' ' . ($teacher->middle_name ?? '') . ' ' . ($teacher->last_name ?? '')) }}
              </td>

              <td>{{ $teacher->email }}</td>
              <td>{{ $teacher->subject }}</td>
              <td>{{ $teacher->school_year }}</td>

              <td>
                <span class="status-badge {{ strtolower($teacher->status) }}">
                  {{ ucfirst($teacher->status) }}
                </span>
              </td>

              <td class="teacher-actions">
                <button type="button" class="action-btn view" onclick="viewTeacher({{ $teacher->id }})"
                  title="View Teacher">
                  <i class="fa-solid fa-eye"></i>
                </button>

                <button type="button" class="action-btn edit" onclick="editTeacher({{ $teacher->id }})"
                  title="Edit Teacher">
                  <i class="fa-solid fa-pen"></i>
                </button>

                <button type="button" class="action-btn delete" onclick="deleteTeacher({{ $teacher->id }})"
                  title="Delete Teacher">
                  <i class="fa-solid fa-trash"></i>
                </button>

                <button type="button" class="action-btn toggle" onclick="toggleStatus({{ $teacher->id }})"
                  title="Change Status">
                  <i class="fa-solid fa-power-off"></i>
                </button>
              </td>
            </tr>
          @empty
            <tr class="teacher-empty-row">
              <td colspan="7">
                <div class="teacher-empty-state">
                  <div class="teacher-empty-icon">
                    <i class="fa-solid fa-user-tie"></i>
                  </div>

                  <h3>No Teacher Records Yet</h3>

                  <p>
                    There are currently no teacher records available.
                    Add a teacher to get started.
                  </p>

                  <button type="button" class="empty-state-add-btn"
                    onclick="document.getElementById('addTeacherBtn').click()">
                    <i class="fa-solid fa-plus"></i>
                    Add Teacher
                  </button>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pagination">
      <button class="page-btn" type="button">
        <i class="fa-solid fa-chevron-left"></i>
      </button>
      <button class="page-btn active" type="button">1</button>
      <button class="page-btn" type="button">2</button>
      <button class="page-btn" type="button">3</button>
      <button class="page-btn" type="button">4</button>
      <button class="page-btn" type="button">5</button>
      <button class="page-btn" type="button">
        <i class="fa-solid fa-chevron-right"></i>
      </button>
    </div>

  </section>

  <!-- ADD TEACHER MODAL -->
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
          <input type="text" id="teacherId" placeholder="Enter teacher ID" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="teacherFirstName">First Name</label>
            <input type="text" id="teacherFirstName" placeholder="Enter first name" required>
          </div>

          <div class="form-group">
            <label for="teacherMiddleName">Middle Name</label>
            <input type="text" id="teacherMiddleName" placeholder="Enter middle name">
          </div>
        </div>

        <div class="form-group">
          <label for="teacherLastName">Last Name</label>
          <input type="text" id="teacherLastName" placeholder="Enter last name" required>
        </div>

        <div class="form-group">
          <label for="teacherEmail">Email Address</label>
          <input type="email" id="teacherEmail" placeholder="Enter email address" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="teacherSubject">Subject Assigned</label>
            <select id="teacherSubject" required>
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
            <select id="teacherSchoolYear" required>
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
        <button type="button" class="cancel-btn" id="cancelTeacherModal">
          Cancel
        </button>

        <button type="button" class="save-teacher-btn" id="saveTeacherBtn">
          <i class="fa-solid fa-plus"></i>
          Add Teacher
        </button>
      </div>

    </div>
  </div>

  <!-- EDIT TEACHER MODAL -->
  <div class="modal-overlay" id="editTeacherModal">
    <div class="teacher-modal">

      <div class="modal-header">
        <div>
          <h2>Edit Teacher</h2>
          <p>Update teacher record information.</p>
        </div>

        <button type="button" class="modal-close" id="closeEditModal" title="Close">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="modal-body">
        <div class="form-group">
          <label for="editTeacherId">Teacher ID</label>
          <input type="text" id="editTeacherId" required>
        </div>

        <div class="form-group">
          <label for="editTeacherName">Teacher Name</label>
          <input type="text" id="editTeacherName" required>
        </div>

        <div class="form-group">
          <label for="editEmail">Email Address</label>
          <input type="email" id="editEmail" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="editSubject">Subject Assigned</label>
            <select id="editSubject" required>
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
            <select id="editSchoolYear" required>
              <option value="2026-2027">2026-2027</option>
              <option value="2027-2028">2027-2028</option>
            </select>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="cancel-btn" id="cancelEditModal">
          Cancel
        </button>

        <button type="button" class="save-teacher-btn" id="saveEditTeacher">
          <i class="fa-solid fa-floppy-disk"></i>
          Save Changes
        </button>
      </div>

    </div>
  </div>

  <!-- VIEW TEACHER MODAL -->
  <div class="modal-overlay" id="viewTeacherModal">
    <div class="teacher-modal">

      <div class="modal-header">
        <div>
          <h2>Teacher Information</h2>
          <p>View teacher record details.</p>
        </div>

        <button type="button" class="modal-close" id="closeViewModal" title="Close">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="modal-body">
        <div class="form-group">
          <label>Teacher ID</label>
          <p id="viewTeacherId">-</p>
        </div>

        <div class="form-group">
          <label>Teacher Name</label>
          <p id="viewTeacherName">-</p>
        </div>

        <div class="form-group">
          <label>Email Address</label>
          <p id="viewEmail">-</p>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Subject Assigned</label>
            <p id="viewSubject">-</p>
          </div>

          <div class="form-group">
            <label>School Year</label>
            <p id="viewSchoolYear">-</p>
          </div>
        </div>

        <div class="form-group">
          <label>Status</label>
          <p id="viewStatus">-</p>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="cancel-btn" id="closeViewModalBtn">
          Close
        </button>
      </div>

    </div>
  </div>

  <!-- DELETE TEACHER MODAL -->
  <div class="modal-overlay" id="deleteTeacherModal">
    <div class="teacher-modal delete-modal">

      <div class="modal-header">
        <div>
          <h2>Delete Teacher</h2>
          <p>Remove teacher record.</p>
        </div>

        <button type="button" class="modal-close" id="closeDeleteModal" title="Close">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="delete-body">
        <div class="delete-icon">
          <i class="fa-solid fa-trash"></i>
        </div>

        <h3>Delete this teacher?</h3>

        <p>
          Are you sure you want to delete
          <strong id="deleteTeacherName">this teacher</strong>?
          This action cannot be undone.
        </p>
      </div>

      <div class="modal-footer">
        <button type="button" class="cancel-btn" id="cancelDeleteModal">
          Cancel
        </button>

        <button type="button" class="delete-confirm-btn" id="confirmDeleteTeacher">
          <i class="fa-solid fa-trash"></i>
          Delete
        </button>
      </div>

    </div>
  </div>

@endsection


@section('page-js')
    <script src="{{ asset('js/teacher-management.js') }}" defer></script>
@endsection --}}