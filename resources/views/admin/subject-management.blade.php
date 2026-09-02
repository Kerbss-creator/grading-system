```blade
@extends('layouts.admin')

@section('title', 'Subject Management')

@section('header', 'Subject Management')

@section('header-description', 'Manage subjects and subject information.')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('css/subject-management-admin.css') }}">
@endsection

@section('content')

  <section class="subject-management">

    <!-- Toolbar -->
    <div class="subject-toolbar">

      <!-- Add Subject -->
      <button class="add-subject-btn" type="button" id="addSubjectBtn">

        <i class="fa-solid fa-plus"></i>

        Add Subject

      </button>

      <!-- Search + Filters -->
      <div class="toolbar-right">

        <!-- Search -->
        <div class="search-box">

          <i class="fa-solid fa-magnifying-glass"></i>

          <input type="text" id="subjectSearch" placeholder="Search subject...">

        </div>

        <!-- Grade Level Filter -->
        <select class="filter-select" id="gradeFilter">

          <option value="">
            All Grade Levels
          </option>

          <option value="7">
            Grade 7
          </option>

          <option value="8">
            Grade 8
          </option>

          <option value="9">
            Grade 9
          </option>

          <option value="10">
            Grade 10
          </option>

        </select>

        <!-- Status Filter -->
        <select class="filter-select" id="statusFilter">

          <option value="">
            All Status
          </option>

          <option value="active">
            Active
          </option>

          <option value="inactive">
            Inactive
          </option>

        </select>

      </div>

    </div>


    <!-- Subject Table -->
    <div class="table-container">

      <table class="subject-table">

        <thead>

          <tr>

            <th>Subject Name</th>

            <th>Grade Level</th>

            <th>School Year</th>

            <th>Status</th>

            <th>Action</th>

          </tr>

        </thead>

        <tbody id="subjectTableBody">

          <!-- Subject 1 -->
          <tr>

            <td>Mathematics</td>

            <td>Grade 7</td>

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


          <!-- Subject 2 -->
          <tr>

            <td>Science</td>

            <td>Grade 7</td>

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


          <!-- Subject 3 -->
          <tr>

            <td>English</td>

            <td>Grade 8</td>

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


          <!-- Subject 4 -->
          <tr>

            <td>Filipino</td>

            <td>Grade 9</td>

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


  <!-- ADD SUBJECT MODAL -->

  <div class="modal-overlay" id="subjectModal">

    <div class="subject-modal">

      <!-- Modal Header -->
      <div class="modal-header">

        <div>

          <h2>Add Subject</h2>

          <p>
            Create a new subject record.
          </p>

        </div>

        <button type="button" class="modal-close" id="closeSubjectModal" title="Close">

          <i class="fa-solid fa-xmark"></i>

        </button>

      </div>


      <!-- Modal Body -->
      <div class="modal-body">

        <!-- Subject Name -->
        <div class="form-group">

          <label for="subjectName">
            Subject Name
          </label>

          <input type="text" id="subjectName" placeholder="Enter subject name">

        </div>


        <!-- Grade Level + School Year -->
        <div class="form-row">

          <div class="form-group">

            <label for="subjectGrade">
              Grade Level
            </label>

            <select id="subjectGrade">

              <option value="">
                Select Grade
              </option>

              <option value="7">
                Grade 7
              </option>

              <option value="8">
                Grade 8
              </option>

              <option value="9">
                Grade 9
              </option>

              <option value="10">
                Grade 10
              </option>

            </select>

          </div>


          <div class="form-group">

            <label for="subjectSchoolYear">
              School Year
            </label>

            <select id="subjectSchoolYear">

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

          <label for="subjectStatus">
            Status
          </label>

          <select id="subjectStatus">

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

        <button type="button" class="cancel-btn" id="cancelSubjectModal">

          Cancel

        </button>

        <button type="button" class="save-subject-btn">

          <i class="fa-solid fa-plus"></i>

          Add Subject

        </button>

      </div>

    </div>

  </div>

@endsection


@section('page-js')

  <script src="{{ asset('js/subject-management-admin.js') }}"></script>

@endsection
