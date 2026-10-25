@extends('layouts.admin')
@section('title', 'Student Management')
@section('header', 'Student Management')
@section('header-description', 'Manage student records, information, and accounts.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/studentmanagement.css') }}">
@endsection

@section('content')

  <!-- STUDENT MANAGEMENT -->

  <section class="student-management">

    <!-- TOOLBAR -->

    <div class="student-toolbar">

      <!-- ADD STUDENT -->

      <button class="add-student-btn" type="button" id="addStudentBtn">

        <i class="fa-solid fa-plus"></i>

        Add Student

      </button>


      <!-- SEARCH AND FILTERS -->

      <div class="toolbar-right">

        <!-- SEARCH -->

        <div class="search-box">

          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" id="studentSearch" placeholder="Search student...">

        </div>


        <!-- GRADE FILTER -->

        <select class="filter-select" id="gradeFilter">
          <option value="all">All Grades</option>
          <option value="Grade 7">Grade 7</option>
          <option value="Grade 8">Grade 8</option>
          <option value="Grade 9">Grade 9</option>
          <option value="Grade 10">Grade 10</option>
        </select>


        <!-- SECTION FILTER -->

        <select class="filter-select" id="sectionFilter">
          <option value="all">All Gender</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
      </select>

      </div>
    </div>


    <!-- STUDENT TABLE -->

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
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="studentTableBody">
                @foreach($students as $student)
                <tr data-id="{{ $student->id }}" 
                    data-student-id="{{ $student->student_id }}"
                    data-first-name="{{ $student->first_name }}"
                    data-middle-name="{{ $student->middle_name }}"
                    data-last-name="{{ $student->last_name }}"
                    data-grade-level="{{ $student->grade_level }}"
                    data-sex="{{ $student->sex }}"
                    data-school-year="{{ $student->school_year }}"
                    data-email="{{ $student->email }}"
                    data-status="{{ $student->status }}"
                    style="{{ $student->status === 'inactive' ? 'opacity: 0.5;' : '' }}">
                    <td>{{ $student->student_id }}</td>
                    <td>{{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }}</td>
                    <td>{{ $student->grade_level }}</td>
                    <td>{{ $student->sex }}</td>
                    <td>{{ $student->school_year }}</td>
                    <td>{{ $student->email }}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="view-btn" type="button" title="View Student" onclick="viewStudent({{ $student->id }})">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button class="edit-btn" type="button" title="Edit Student" onclick="editStudent({{ $student->id }})">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="delete-btn" type="button" title="Delete Student" onclick="deleteStudent({{ $student->id }})">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                            <button class="action-btn {{ $student->status === 'active' ? 'reset' : 'edit' }}" 
                                    type="button" 
                                    title="{{ $student->status === 'active' ? 'Hide' : 'Unhide' }}" 
                                    onclick="toggleStatus({{ $student->id }})"
                                    style="background: #f0f0f0; color: #555;">
                                <i class="fa-solid {{ $student->status === 'active' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>


    <!-- PAGINATION -->

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


  <!-- ADD STUDENT MODAL -->
<div class="modal-overlay" id="studentModal">
    <div class="student-modal">
        <div class="modal-header">
            <div>
                <h2>Add Student</h2>
                <p>Create a new student record.</p>
            </div>
            <button class="modal-close" id="closeStudentModal" type="button" title="Close">
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
                <label for="email">Gmail</label>
                <!-- CHANGED: type="text" to prevent browser's native email validation popup -->
                <input type="text" id="email" placeholder="Enter gmail address">
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
            <button type="button" class="cancel-btn" id="cancelStudentModal">Cancel</button>
            <button type="button" class="save-student-btn" id="saveStudentBtn">
                <i class="fa-solid fa-plus"></i> Add Student
            </button>
        </div>
    </div>
</div>

  <!-- VIEW STUDENT MODAL -->
<div class="modal-overlay" id="viewStudentModal">
    <div class="student-modal view-modal">
        <div class="modal-header">
            <div>
                <h2>Student Information</h2>
                <p>View student record details.</p>
            </div>
            <button class="modal-close" id="closeViewModal" type="button" title="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="student-info">
                <div class="info-group">
                    <span>Student ID</span>
                    <strong id="viewStudentId">-</strong>
                </div>
                <div class="info-group">
                    <span>Student Name</span>
                    <strong id="viewStudentName">-</strong>
                </div>
                <div class="info-group">
                    <span>Grade Level</span>
                    <strong id="viewGradeLevel">-</strong>
                </div>
                <div class="info-group">
                    <span>Sex</span>
                    <strong id="viewSex">-</strong>
                </div>
                <div class="info-group">
                    <span>School Year</span>
                    <strong id="viewSchoolYear">-</strong>
                </div>
                <div class="info-group">
                    <span>Gmail</span>
                    <strong id="viewGmail">-</strong>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="cancel-btn" id="closeViewModalBtn">Close</button>
        </div>
    </div>
</div>


  <!-- EDIT STUDENT MODAL -->
 <div class="modal-overlay" id="editStudentModal">
    <div class="student-modal">
        <div class="modal-header">
            <div>
                <h2>Edit Student</h2>
                <p>Update student record information.</p>
            </div>
            <button class="modal-close" id="closeEditModal" type="button" title="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="editStudentId">Student ID</label>
                <input type="text" id="editStudentId">
            </div>
            <div class="form-group">
                <label for="editStudentName">Student Name</label>
                <input type="text" id="editStudentName">
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
                <label for="editGmail">Gmail</label>
                <!-- CHANGED: type="text" to prevent browser's native email validation popup -->
                <input type="text" id="editGmail">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="cancel-btn" id="cancelEditModal">Cancel</button>
            <button type="button" class="save-student-btn" id="saveEditStudent">
                <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
        </div>
    </div>
</div>


  <!-- DELETE STUDENT MODAL -->
<div class="modal-overlay" id="deleteStudentModal">
    <div class="student-modal delete-modal">
        <div class="modal-header">
            <div>
                <h2>Delete Student</h2>
                <p>Remove student record.</p>
            </div>
            <button class="modal-close" id="closeDeleteModal" type="button" title="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="delete-body">
            <div class="delete-icon">
                <i class="fa-solid fa-trash"></i>
            </div>
            <h3>Delete this student?</h3>
            <p>
                Are you sure you want to delete
                <strong id="deleteStudentName">this student</strong>?
                This action cannot be undone.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="cancel-btn" id="cancelDeleteModal">Cancel</button>
            <button type="button" class="delete-confirm-btn" id="confirmDeleteStudent">
                <i class="fa-solid fa-trash"></i> Delete
            </button>
        </div>
    </div>
</div>
@endsection

@section('page-js')
<script src="{{ asset('js/admin/student-management.js') }}"></script>
<script>
    // Safely override native alert to use custom modal
    // This contains ZERO complex Blade directives to guarantee no PHP ParseErrors
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