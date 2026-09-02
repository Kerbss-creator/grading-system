
@extends('layouts.admin')

@section('title', 'Teacher Management')

@section('header', 'Teacher Management')

@section('header-description', 'Manage teacher records, information, and accounts.')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('css/teacher-management-admin.css') }}">
@endsection

@section('content')

  <section class="teacher-management">

    <!-- Toolbar -->
    <div class="teacher-toolbar">

      <button class="add-teacher-btn" type="button" id="addTeacherBtn">

        <i class="fa-solid fa-plus"></i>

        Add Teacher

      </button>

      <div class="toolbar-right">

        <!-- Search -->
        <div class="search-box">

          <i class="fa-solid fa-magnifying-glass"></i>

          <input type="text" id="teacherSearch" placeholder="Search teacher...">

        </div>

        <!-- Subject Filter -->
        <select class="filter-select" id="subjectFilter">

          <option value="">All Subjects</option>
          <option value="Mathematics">Mathematics</option>
          <option value="Science">Science</option>
          <option value="English">English</option>
          <option value="Filipino">Filipino</option>

        </select>

        <!-- Status Filter -->
        <select class="filter-select" id="statusFilter">

          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>

        </select>

      </div>

    </div>

    <!-- Teacher Table -->
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

          <!-- Teacher 1 -->
          <tr>

            <td>080912</td>

            <td>Mr. John Santos</td>

            <td>john.santos@forbes.edu.ph</td>

            <td>Mathematics</td>

            <td>2026-2027</td>

            <td>
              <span class="status active">
                Active
              </span>
            </td>

            <td>

              <div class="action-buttons">

                <button class="view-btn" type="button" title="View">

                  <i class="fa-solid fa-eye"></i>

                </button>

                <button class="edit-btn" type="button" title="Edit">

                  <i class="fa-solid fa-pen"></i>

                </button>

                <button class="delete-btn" type="button" title="Delete">

                  <i class="fa-solid fa-trash"></i>

                </button>

              </div>

            </td>

          </tr>

          <!-- Teacher 2 -->
          <tr>

            <td>080913</td>

            <td>Ms. Maria Cruz</td>

            <td>maria.cruz@forbes.edu.ph</td>

            <td>Science</td>

            <td>2026-2027</td>

            <td>
              <span class="status active">
                Active
              </span>
            </td>

            <td>

              <div class="action-buttons">

                <button class="view-btn" type="button" title="View">

                  <i class="fa-solid fa-eye"></i>

                </button>

                <button class="edit-btn" type="button" title="Edit">

                  <i class="fa-solid fa-pen"></i>

                </button>

                <button class="delete-btn" type="button" title="Delete">

                  <i class="fa-solid fa-trash"></i>

                </button>

              </div>

            </td>

          </tr>

          <!-- Teacher 3 -->
          <tr>

            <td>080914</td>

            <td>Mr. Pedro Reyes</td>

            <td>pedro.reyes@forbes.edu.ph</td>

            <td>English</td>

            <td>2026-2027</td>

            <td>
              <span class="status inactive">
                Inactive
              </span>
            </td>

            <td>

              <div class="action-buttons">

                <button class="view-btn" type="button" title="View">

                  <i class="fa-solid fa-eye"></i>

                </button>

                <button class="edit-btn" type="button" title="Edit">

                  <i class="fa-solid fa-pen"></i>

                </button>

                <button class="delete-btn" type="button" title="Delete">

                  <i class="fa-solid fa-trash"></i>

                </button>

              </div>

            </td>

          </tr>

        </tbody>

      </table>

    </div>

    <!-- Pagination -->
    <div class="pagination">

      <button class="page-btn" type="button">

        <i class="fa-solid fa-chevron-left"></i>

      </button>

      <button class="page-btn active" type="button">

        1

      </button>

      <button class="page-btn" type="button">

        2

      </button>

      <button class="page-btn" type="button">

        3

      </button>

      <button class="page-btn" type="button">

        4

      </button>

      <button class="page-btn" type="button">

        5

      </button>

      <button class="page-btn" type="button">

        <i class="fa-solid fa-chevron-right"></i>

      </button>

    </div>

  </section>


  <!-- ADD TEACHER MODAL -->

  <div class="modal-overlay" id="teacherModal">

    <div class="teacher-modal">

      <!-- Modal Header -->
      <div class="modal-header">

        <div>

          <h2>Add Teacher</h2>

          <p>Create a new teacher record.</p>

        </div>

        <button type="button" class="modal-close" id="closeTeacherModal" title="Close">

          <i class="fa-solid fa-xmark"></i>

        </button>

      </div>

      <!-- Modal Body -->
      <div class="modal-body">

        <!-- Teacher ID -->
        <div class="form-group">

          <label for="teacherId">
            Teacher ID
          </label>

          <input type="text" id="teacherId" placeholder="Enter teacher ID">

        </div>

        <!-- First + Middle Name -->
        <div class="form-row">

          <div class="form-group">

            <label for="teacherFirstName">
              First Name
            </label>

            <input type="text" id="teacherFirstName" placeholder="Enter first name">

          </div>

          <div class="form-group">

            <label for="teacherMiddleName">
              Middle Name
            </label>

            <input type="text" id="teacherMiddleName" placeholder="Enter middle name">

          </div>

        </div>

        <!-- Last Name -->
        <div class="form-group">

          <label for="teacherLastName">
            Last Name
          </label>

          <input type="text" id="teacherLastName" placeholder="Enter last name">

        </div>

        <!-- Email -->
        <div class="form-group">

          <label for="teacherEmail">
            Email Address
          </label>

          <input type="email" id="teacherEmail" placeholder="Enter email address">

        </div>

        <!-- Subject + School Year -->
        <div class="form-row">

          <div class="form-group">

            <label for="teacherSubject">
              Subject Assigned
            </label>

            <select id="teacherSubject">

              <option value="">
                Select Subject
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

              <option value="Araling Panlipunan">
                Araling Panlipunan
              </option>

              <option value="MAPEH">
                MAPEH
              </option>

              <option value="TLE">
                TLE
              </option>

            </select>

          </div>

          <div class="form-group">

            <label for="teacherSchoolYear">
              School Year
            </label>

            <select id="teacherSchoolYear">

              <option value="">
                Select School Year
              </option>

              <option value="2026-2027">
                2026-2027
              </option>

              <option value="2027-2028">
                2027-2028
              </option>

            </select>

          </div>

        </div>

        <!-- Status -->
        <div class="form-group">

          <label for="teacherStatus">
            Status
          </label>

          <select id="teacherStatus">

            <option value="active">
              Active
            </option>

            <option value="inactive">
              Inactive
            </option>

          </select>

        </div>

      </div>

      <!-- Modal Footer -->
      <div class="modal-footer">

        <button type="button" class="cancel-btn" id="cancelTeacherModal">

          Cancel

        </button>

        <button type="button" class="save-teacher-btn">

          <i class="fa-solid fa-plus"></i>

          Add Teacher

        </button>

      </div>

    </div>

  </div>

@endsection


@section('page-js')

  <script src="{{ asset('js/teacher-management-admin.js') }}"></script>

@endsection