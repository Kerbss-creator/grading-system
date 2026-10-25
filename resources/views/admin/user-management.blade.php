@extends('layouts.admin')

@section('title', 'Forbes Academy | User Management')
@section('header', 'User Management')
@section('header-description', 'Manage student, teacher, adviser, and admin accounts.')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('css/admin/user-management.css') }}">
@endsection

@section('content')
  <section class="user-management-container">

    <div class="user-stats">
      <div class="user-stat-card">
        <div class="user-stat-icon total"><i class="fa-solid fa-users"></i></div>
        <div><span>Total Users</span>
          <h3 id="totalUsers">0</h3>
        </div>
      </div>

      <div class="user-stat-card">
        <div class="user-stat-icon student"><i class="fa-solid fa-user-graduate"></i></div>
        <div><span>Students</span>
          <h3 id="totalStudents">0</h3>
        </div>
      </div>

      <div class="user-stat-card">
        <div class="user-stat-icon teacher"><i class="fa-solid fa-chalkboard-user"></i></div>
        <div><span>Teachers</span>
          <h3 id="totalTeachers">0</h3>
        </div>
      </div>

      <div class="user-stat-card">
        <div class="user-stat-icon adviser"><i class="fa-solid fa-user-tie"></i></div>
        <div><span>Advisers</span>
          <h3 id="totalAdvisers">0</h3>
        </div>
      </div>

      <div class="user-stat-card">
        <div class="user-stat-icon admin"><i class="fa-solid fa-user-shield"></i></div>
        <div><span>Admins</span>
          <h3 id="totalAdmins">0</h3>
        </div>
      </div>
    </div>

    <div class="content-card">
      <div class="card-header">
        <div>
          <h2>System Users</h2>
          <p>List of registered system accounts.</p>
        </div>

        <button class="add-user-btn" id="openAddUser" type="button">
          <i class="fa-solid fa-plus"></i>
          Add User
        </button>
      </div>

      <div class="user-toolbar">
        <div class="filter-group">
          <label for="roleFilter">Filter by Role</label>
          <select id="roleFilter">
            <option value="all">All Roles</option>
            <option value="Student">Student</option>
            <option value="Teacher">Teacher</option>
            <option value="Adviser">Adviser</option>
            <option value="Admin">Admin</option>
          </select>
        </div>

        <div class="search-box">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="search" id="searchInput" placeholder="Search users..." aria-label="Search users">
        </div>
      </div>

      <div class="table-container">
        <table class="user-table">
          <thead>
            <tr>
              <th>User</th>
              <th>Username</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>

          <tbody id="userTableBody">
            <tr class="empty-state-row" id="emptyStateRow">
              <td colspan="6">
                <div class="user-empty-state">
                  <div class="empty-state-icon">
                    <i class="fa-solid fa-users"></i>
                  </div>
                  <h3 id="emptyStateTitle">No Users Yet</h3>
                  <p id="emptyStateMessage">
                    There are no user accounts available. Add your first user to get started.
                  </p>
                  <button type="button" class="empty-state-add-btn" id="emptyAddUser">
                    <i class="fa-solid fa-plus"></i>
                    Add User
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="table-footer">
        <span id="tableResultCount">Showing 0 users</span>
        <span class="table-note">
          <i class="fa-solid fa-circle-info"></i>
          User accounts are temporary until connected to the database.
        </span>
      </div>
    </div>
  </section>

  <!-- Add and Edit User Modal -->
  <div class="modal-overlay" id="userModal" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">

      <div class="modal-header">
        <div>
          <h2 id="modalTitle">Add New User</h2>
          <p id="modalDescription">Create a new system account.</p>
        </div>
        <button class="close-modal" id="closeModal" type="button" aria-label="Close modal">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="role-selection" id="roleSelection">
        <p class="form-label">Select User Role</p>

        <div class="role-options">
          <button class="role-option" data-role="Student" type="button">
            <i class="fa-solid fa-user-graduate"></i>
            <span>Student</span>
            <small>Student account</small>
          </button>

          <button class="role-option" data-role="Teacher" type="button">
            <i class="fa-solid fa-chalkboard-user"></i>
            <span>Teacher</span>
            <small>Teacher account</small>
          </button>

          <button class="role-option" data-role="Adviser" type="button">
            <i class="fa-solid fa-user-tie"></i>
            <span>Adviser</span>
            <small>Advisory class account</small>
          </button>

          <button class="role-option" data-role="Admin" type="button">
            <i class="fa-solid fa-user-shield"></i>
            <span>Admin</span>
            <small>Administrator account</small>
          </button>
        </div>
      </div>

      <form id="userForm" class="user-form hidden">
        <input type="hidden" id="selectedRole" name="role">

        <div class="selected-role">
          <span><i class="fa-solid fa-user" id="selectedRoleIcon"></i></span>
          <div>
            <strong id="selectedRoleText">Select Role</strong>
            <small>Account information</small>
          </div>
          <button type="button" id="changeRoleBtn" class="change-role-btn">Change Role</button>
        </div>

        <div class="form-group">
          <label for="userId">User ID</label>
          <input type="text" id="userId" name="user_id" placeholder="Generated automatically" readonly required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="firstName">First Name</label>
            <input type="text" id="firstName" name="first_name" placeholder="Enter first name" required>
          </div>
          <div class="form-group">
            <label for="lastName">Last Name</label>
            <input type="text" id="lastName" name="last_name" placeholder="Enter last name" required>
          </div>
        </div>

        <div class="form-group">
          <label for="middleName">Middle Name <span>(Optional)</span></label>
          <input type="text" id="middleName" name="middle_name" placeholder="Enter middle name">
        </div>

        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" placeholder="Enter username" required>
        </div>

        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" placeholder="Enter email address" required>
        </div>

        <div class="role-specific-fields" id="studentFields" hidden>
          <h3 class="role-fields-heading">Student Information</h3>
          <div class="form-row">
            <div class="form-group">
              <label for="gradeLevel">Grade Level</label>
              <select id="gradeLevel" name="grade_level" disabled>
                <option value="">Select Grade</option>
                <option value="7">Grade 7</option>
                <option value="8">Grade 8</option>
                <option value="9">Grade 9</option>
                <option value="10">Grade 10</option>
              </select>
            </div>
            <div class="form-group">
              <label for="section">Section</label>
              <select id="section" name="section" disabled>
                <option value="">Select Section</option>
                <option value="A">Section A</option>
                <option value="B">Section B</option>
                <option value="C">Section C</option>
              </select>
            </div>
          </div>
        </div>

        <div class="role-specific-fields" id="teacherFields" hidden>
          <h3 class="role-fields-heading">Teacher Information</h3>
          <div class="form-group">
            <label for="teacherDepartment">Department</label>
            <input type="text" id="teacherDepartment" name="department" placeholder="Junior High School" disabled>
          </div>
        </div>

        <div class="role-specific-fields" id="adviserFields" hidden>
          <h3 class="role-fields-heading">Adviser Information</h3>
          <div class="form-group">
            <label for="adviserGradeLevel">Assigned Grade Level</label>
            <select id="adviserGradeLevel" name="adviser_grade_level" disabled>
              <option value="">Select Grade Level</option>
              <option value="7">Grade 7</option>
              <option value="8">Grade 8</option>
              <option value="9">Grade 9</option>
              <option value="10">Grade 10</option>
            </select>
          </div>
          <div class="form-group">
            <label for="adviserSection">Assigned Advisory Section</label>
            <select id="adviserSection" name="adviser_section" disabled>
              <option value="">Select Section</option>
              <option value="A">Section A</option>
              <option value="B">Section B</option>
              <option value="C">Section C</option>
            </select>
          </div>
          <p class="form-hint">
            <i class="fa-solid fa-circle-info"></i>
            Assign one advisory class to this adviser.
          </p>
        </div>

        <div class="role-specific-fields" id="adminFields" hidden>
          <h3 class="role-fields-heading">Administrator Information</h3>
          <p class="form-hint">
            <i class="fa-solid fa-circle-info"></i>
            This account is for administrator-level system management.
          </p>
        </div>

        <div class="form-row" id="passwordFields">
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter password" required
              autocomplete="new-password">
          </div>
          <div class="form-group">
            <label for="confirmPassword">Confirm Password</label>
            <input type="password" id="confirmPassword" name="password_confirmation" placeholder="Confirm password"
              required autocomplete="new-password">
          </div>
        </div>

        <div class="form-group">
          <label for="accountStatus">Account Status</label>
          <select id="accountStatus" name="status" required>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>

        <div class="form-actions">
          <button type="button" class="cancel-btn" id="cancelForm">Cancel</button>
          <button type="submit" class="save-btn" id="saveUserBtn">
            <i class="fa-solid fa-user-plus"></i>
            <span id="saveUserText">Save User</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- View User Modal -->
  <div class="modal-overlay" id="viewUserModal" aria-hidden="true">
    <div class="modal view-modal" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle">
      <div class="modal-header">
        <div>
          <h2 id="viewModalTitle">User Details</h2>
          <p>View account information.</p>
        </div>
        <button class="close-modal" data-close-modal="viewUserModal" type="button" aria-label="Close modal">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div class="view-user-body" id="viewUserDetails"></div>
      <div class="modal-footer">
        <button class="cancel-btn" data-close-modal="viewUserModal" type="button">Close</button>
      </div>
    </div>
  </div>

  <!-- Delete User Modal -->
  <div class="modal-overlay" id="deleteUserModal" aria-hidden="true">
    <div class="modal delete-modal" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
      <div class="modal-header">
        <div>
          <h2 id="deleteModalTitle">Delete User</h2>
          <p>Confirm account deletion.</p>
        </div>
        <button class="close-modal" data-close-modal="deleteUserModal" type="button" aria-label="Close modal">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div class="delete-body">
        <div class="delete-icon"><i class="fa-solid fa-trash"></i></div>
        <h3>Are you sure?</h3>
        <p>You are about to delete <strong id="deleteUserName">this user</strong>.</p>
        <p class="delete-warning">This action cannot be undone in this demo.</p>
      </div>
      <div class="modal-footer">
        <button class="cancel-btn" data-close-modal="deleteUserModal" type="button">Cancel</button>
        <button class="delete-confirm-btn" id="confirmDeleteUser" type="button">
          <i class="fa-solid fa-trash"></i> Delete User
        </button>
      </div>
    </div>
  </div>

  <!-- Reset Password Modal -->
  <div class="modal-overlay" id="resetPasswordModal" aria-hidden="true">
    <div class="modal reset-modal" role="dialog" aria-modal="true" aria-labelledby="resetModalTitle">
      <div class="modal-header">
        <div>
          <h2 id="resetModalTitle">Reset Password</h2>
          <p>Set a new password for this account.</p>
        </div>
        <button class="close-modal" data-close-modal="resetPasswordModal" type="button" aria-label="Close modal">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <form id="resetPasswordForm">
        <div class="reset-body">
          <p>Account: <strong id="resetUserName"></strong></p>
          <div class="form-group">
            <label for="newPassword">New Password</label>
            <input type="password" id="newPassword" required minlength="8" autocomplete="new-password"
              placeholder="At least 8 characters">
          </div>
          <div class="form-group">
            <label for="confirmNewPassword">Confirm New Password</label>
            <input type="password" id="confirmNewPassword" required minlength="8" autocomplete="new-password"
              placeholder="Re-enter password">
          </div>
        </div>
        <div class="modal-footer">
          <button class="cancel-btn" data-close-modal="resetPasswordModal" type="button">Cancel</button>
          <button class="save-btn" type="submit">
            <i class="fa-solid fa-key"></i> Reset Password
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection

@section('page-js')
  <script src="{{ asset('js/admin/user-management.js') }}"></script>
@endsection