
@extends('layouts.admin')

@section('title', 'Student Management')

@section('header', 'Student Management')

@section('header-description', 'Manage student records, information, and accounts.')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('css/studentmanagement.css') }}">
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

          <option value="all">All Sections</option>
          <option value="A">Section A</option>
          <option value="B">Section B</option>
          <option value="C">Section C</option>

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
            <th>Section</th>
            <th>School Year</th>
            <th>Status</th>
            <th>Action</th>
          </tr>

        </thead>


        <tbody id="studentTableBody">

          <!-- STUDENT 1 -->

          <tr>

            <td>2026-001</td>

            <td>Juan Dela Cruz</td>

            <td>Grade 7</td>

            <td>A</td>

            <td>2026-2027</td>

            <td>
              <span class="status active">
                Active
              </span>
            </td>

            <td>

              <div class="action-buttons">

                <button class="view-btn" type="button" title="View Student">

                  <i class="fa-solid fa-eye"></i>

                </button>


                <button class="edit-btn" type="button" title="Edit Student">

                  <i class="fa-solid fa-pen"></i>

                </button>


                <button class="delete-btn" type="button" title="Delete Student">

                  <i class="fa-solid fa-trash"></i>

                </button>

              </div>

            </td>

          </tr>


          <!-- STUDENT 2 -->

          <tr>

            <td>2026-002</td>

            <td>Maria Santos</td>

            <td>Grade 8</td>

            <td>B</td>

            <td>2026-2027</td>

            <td>
              <span class="status active">
                Active
              </span>
            </td>

            <td>

              <div class="action-buttons">

                <button class="view-btn" type="button" title="View Student">

                  <i class="fa-solid fa-eye"></i>

                </button>


                <button class="edit-btn" type="button" title="Edit Student">

                  <i class="fa-solid fa-pen"></i>

                </button>


                <button class="delete-btn" type="button" title="Delete Student">

                  <i class="fa-solid fa-trash"></i>

                </button>

              </div>

            </td>

          </tr>


          <!-- STUDENT 3 -->

          <tr>

            <td>2026-003</td>

            <td>Pedro Reyes</td>

            <td>Grade 9</td>

            <td>A</td>

            <td>2026-2027</td>

            <td>
              <span class="status inactive">
                Inactive
              </span>
            </td>

            <td>

              <div class="action-buttons">

                <button class="view-btn" type="button" title="View Student">

                  <i class="fa-solid fa-eye"></i>

                </button>


                <button class="edit-btn" type="button" title="Edit Student">

                  <i class="fa-solid fa-pen"></i>

                </button>


                <button class="delete-btn" type="button" title="Delete Student">

                  <i class="fa-solid fa-trash"></i>

                </button>

              </div>

            </td>

          </tr>

        </tbody>

      </table>

    </div>


    <!-- PAGINATION -->

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


  <!-- ADD STUDENT MODAL -->

  <div class="modal-overlay" id="studentModal">

    <div class="student-modal">


      <!-- MODAL HEADER -->

      <div class="modal-header">

        <div>

          <h2>Add Student</h2>

          <p>Create a new student record.</p>

        </div>


        <button class="modal-close" id="closeStudentModal" type="button" title="Close">

          <i class="fa-solid fa-xmark"></i>

        </button>

      </div>


      <!-- MODAL BODY -->

      <div class="modal-body">


        <!-- STUDENT ID -->

        <div class="form-group">

          <label for="studentId">
            Student ID
          </label>

          <input type="text" id="studentId" placeholder="Enter student ID">

        </div>


        <!-- FIRST + MIDDLE NAME -->

        <div class="form-row">

          <div class="form-group">

            <label for="firstName">
              First Name
            </label>

            <input type="text" id="firstName" placeholder="Enter first name">

          </div>


          <div class="form-group">

            <label for="middleName">
              Middle Name
            </label>

            <input type="text" id="middleName" placeholder="Enter middle name">

          </div>

        </div>


        <!-- LAST NAME -->

        <div class="form-group">

          <label for="lastName">
            Last Name
          </label>

          <input type="text" id="lastName" placeholder="Enter last name">

        </div>


        <!-- GRADE + SECTION -->

        <div class="form-row">

          <div class="form-group">

            <label for="gradeLevel">
              Grade Level
            </label>

            <select id="gradeLevel">

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

            <label for="section">
              Section
            </label>

            <select id="section">

              <option value="">
                Select Section
              </option>

              <option value="A">
                Section A
              </option>

              <option value="B">
                Section B
              </option>

              <option value="C">
                Section C
              </option>

            </select>

          </div>

        </div>


        <!-- SCHOOL YEAR -->

        <div class="form-group">

          <label for="schoolYear">
            School Year
          </label>

          <select id="schoolYear">

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


        <!-- STATUS -->

        <div class="form-group">

          <label for="studentStatus">
            Status
          </label>

          <select id="studentStatus">

            <option value="active">
              Active
            </option>

            <option value="inactive">
              Inactive
            </option>

          </select>

        </div>

      </div>


      <!-- MODAL FOOTER -->

      <div class="modal-footer">

        <button type="button" class="cancel-btn" id="cancelStudentModal">

          Cancel

        </button>


        <button type="button" class="save-student-btn">

          <i class="fa-solid fa-plus"></i>

          Add Student

        </button>

      </div>

    </div>

  </div>

@endsection


@section('page-js')

  <script src="{{ asset('js/student-management.js') }}"></script>

@endsection
```