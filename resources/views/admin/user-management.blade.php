@extends('layouts.admin')

@section('title', 'Forbes Academy | User Management')

@section('header', 'User Management')
@section('header-description', 'Manage student and teacher accounts.')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('css/usermngadmin.css') }}">
@endsection

@section('content')

  <section class="user-management-container">

    <!-- Page Intro -->

    <div class="page-intro">
      <div>
        <h2>Manage Users</h2>
        <p>View, add, edit, and manage system user accounts.</p>
      </div>
    </div>

    <!-- User Statistics -->

    <div class="user-stats">

      <!-- Total Users -->

      <div class="user-stat-card">
        <div class="user-stat-icon total">
          <i class="fa-solid fa-users"></i>
        </div>

        <div>
          <span>Total Users</span>
          <h3 id="totalUsers">710</h3>
        </div>
      </div>

      <!-- Students -->

      <div class="user-stat-card">
        <div class="user-stat-icon student">
          <i class="fa-solid fa-user-graduate"></i>
        </div>

        <div>
          <span>Students</span>
          <h3 id="totalStudents">668</h3>
        </div>
      </div>

      <!-- Teachers -->

      <div class="user-stat-card">
        <div class="user-stat-icon teacher">
          <i class="fa-solid fa-chalkboard-user"></i>
        </div>

        <div>
          <span>Teachers</span>
          <h3 id="totalTeachers">42</h3>
        </div>
      </div>

    </div>

    <!-- User Table Card -->

    <div class="content-card">

      <!-- Card Header -->

      <div class="card-header">

        <div>
          <h2>System Users</h2>
          <p>List of registered teachers and students.</p>
        </div>

        <button class="add-user-btn" id="openAddUser" type="button">
          <i class="fa-solid fa-plus"></i>
          Add User
        </button>

      </div>

      <!-- Toolbar -->

      <div class="user-toolbar">

        <!-- Role Filter -->

        <div class="filter-group">

          <label for="roleFilter">Role</label>

          <select id="roleFilter">
            <option value="all">All Roles</option>
            <option value="Student">Student</option>
            <option value="Teacher">Teacher</option>
          </select>

        </div>

        <!-- Search -->

        <div class="search-box">

          <i class="fa-solid fa-magnifying-glass"></i>

          <input type="text" id="searchInput" placeholder="Search users...">

        </div>

      </div>

      <!-- Table -->

      <div class="table-container">

        <table>

          <thead>
            <tr>
              <th>User ID</th>
              <th>Name</th>
              <th>Username</th>
              <th>Email</th>
              <th>Role</th>
              <th>Actions</th>
            </tr>
          </thead>

          <tbody id="userTableBody">

            <!-- Student 1 -->

            <tr>

              <td>STU-001</td>

              <td>
                <div class="user-name">
                  <div class="user-avatar student-avatar">
                    JD
                  </div>

                  <span>Juan Dela Cruz</span>
                </div>
              </td>

              <td>juan.delacruz</td>

              <td>juan@example.com</td>

              <td>
                <span class="role-badge student">
                  Student
                </span>
              </td>

              <td>

                <div class="action-buttons">

                  <button class="action-btn edit" title="Edit User" type="button">
                    <i class="fa-solid fa-pen"></i>
                  </button>

                  <button class="action-btn reset" title="Reset Password" type="button">
                    <i class="fa-solid fa-key"></i>
                  </button>

                  <button class="action-btn delete" title="Delete User" type="button">
                    <i class="fa-solid fa-trash"></i>
                  </button>

                </div>

              </td>

            </tr>

            <!-- Student 2 -->

            <tr>

              <td>STU-002</td>

              <td>
                <div class="user-name">
                  <div class="user-avatar student-avatar">
                    AS
                  </div>

                  <span>Ana Santos</span>
                </div>
              </td>

              <td>ana.santos</td>

              <td>ana@example.com</td>

              <td>
                <span class="role-badge student">
                  Student
                </span>
              </td>

              <td>

                <div class="action-buttons">

                  <button class="action-btn edit" title="Edit User" type="button">
                    <i class="fa-solid fa-pen"></i>
                  </button>

                  <button class="action-btn reset" title="Reset Password" type="button">
                    <i class="fa-solid fa-key"></i>
                  </button>

                  <button class="action-btn delete" title="Delete User" type="button">
                    <i class="fa-solid fa-trash"></i>
                  </button>

                </div>

              </td>

            </tr>

            <!-- Teacher 1 -->

            <tr>

              <td>TEA-001</td>

              <td>
                <div class="user-name">
                  <div class="user-avatar teacher-avatar">
                    MR
                  </div>

                  <span>Maria Reyes</span>
                </div>
              </td>

              <td>maria.reyes</td>

              <td>maria@example.com</td>

              <td>
                <span class="role-badge teacher">
                  Teacher
                </span>
              </td>

              <td>

                <div class="action-buttons">

                  <button class="action-btn edit" title="Edit User" type="button">
                    <i class="fa-solid fa-pen"></i>
                  </button>

                  <button class="action-btn reset" title="Reset Password" type="button">
                    <i class="fa-solid fa-key"></i>
                  </button>

                  <button class="action-btn delete" title="Delete User" type="button">
                    <i class="fa-solid fa-trash"></i>
                  </button>

                </div>

              </td>

            </tr>

            <!-- Teacher 2 -->

            <tr>

              <td>TEA-002</td>

              <td>
                <div class="user-name">
                  <div class="user-avatar teacher-avatar">
                    RP
                  </div>

                  <span>Robert Perez</span>
                </div>
              </td>

              <td>robert.perez</td>

              <td>robert@example.com</td>

              <td>
                <span class="role-badge teacher">
                  Teacher
                </span>
              </td>

              <td>

                <div class="action-buttons">

                  <button class="action-btn edit" title="Edit User" type="button">
                    <i class="fa-solid fa-pen"></i>
                  </button>

                  <button class="action-btn reset" title="Reset Password" type="button">
                    <i class="fa-solid fa-key"></i>
                  </button>

                  <button class="action-btn delete" title="Delete User" type="button">
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

        <span>...</span>

        <button class="page-btn" type="button">
          10
        </button>

        <button class="page-btn" type="button">
          <i class="fa-solid fa-chevron-right"></i>
        </button>

      </div>

    </div>
    ```

  </section>

  <!-- Add User Modal -->

  <div class="modal-overlay" id="userModal">

    ```
    <div class="modal">

      <!-- Modal Header -->

      <div class="modal-header">

        <div>
          <h2>Add User</h2>
          <p>Create a new system account.</p>
        </div>

        <button class="close-modal" id="closeModal" type="button">
          <i class="fa-solid fa-xmark"></i>
        </button>

      </div>


      <!-- Role Selection -->

      <div class="role-selection" id="roleSelection">

        <p class="form-label">
          Select User Role
        </p>

        <div class="role-options">

          <!-- Student -->

          <button class="role-option" data-role="student" type="button">
            <i class="fa-solid fa-user-graduate"></i>

            <span>Student</span>

            <small>
              Student account
            </small>
          </button>


          <!-- Teacher -->

          <button class="role-option" data-role="teacher" type="button">
            <i class="fa-solid fa-chalkboard-user"></i>

            <span>Teacher</span>

            <small>
              Teacher account
            </small>
          </button>


          <!-- Admin -->

          <button class="role-option" data-role="admin" type="button">
            <i class="fa-solid fa-user-shield"></i>

            <span>Admin</span>

            <small>
              Admin account
            </small>
          </button>

        </div>

      </div>


      <!-- User Form -->

      <form id="userForm" class="user-form hidden">

        <!-- Selected Role -->

        <div class="selected-role">

          <span id="selectedRoleIcon">
            <i class="fa-solid fa-user"></i>
          </span>

          <div>
            <strong id="selectedRoleText">
              Student
            </strong>

            <small>
              Account information
            </small>
          </div>

        </div>


        <!-- User ID -->

        <div class="form-group">

          <label for="userId">
            User ID
          </label>

          <input type="text" id="userId" placeholder="e.g. STU-003" required>

        </div>


        <!-- Full Name -->

        <div class="form-group">

          <label for="fullName">
            Full Name
          </label>

          <input type="text" id="fullName" placeholder="Enter full name" required>

        </div>


        <!-- Username -->

        <div class="form-group">

          <label for="username">
            Username
          </label>

          <input type="text" id="username" placeholder="Enter username" required>

        </div>


        <!-- Email -->

        <div class="form-group">

          <label for="email">
            Email
          </label>

          <input type="email" id="email" placeholder="Enter email address" required>

        </div>


        <!-- Password -->

        <div class="form-row">

          <div class="form-group">

            <label for="password">
              Password
            </label>

            <input type="password" id="password" placeholder="Enter password" required>

          </div>


          <div class="form-group">

            <label for="confirmPassword">
              Confirm Password
            </label>

            <input type="password" id="confirmPassword" placeholder="Confirm password" required>

          </div>

        </div>


        <!-- Student Fields -->

        <div class="student-fields" id="studentFields">

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

              <input type="text" id="section" placeholder="e.g. Rizal">

            </div>

          </div>

        </div>


        <!-- Form Buttons -->

        <div class="form-actions">

          <button type="button" class="cancel-btn" id="cancelForm">
            Cancel
          </button>

          <button type="submit" class="save-btn">
            <i class="fa-solid fa-user-plus"></i>
            Create Account
          </button>

        </div>

      </form>

    </div>
    ```

  </div>

@endsection

@section('page-js')

  <script src="{{ asset('js/user-management.js') }}"></script>

@endsection