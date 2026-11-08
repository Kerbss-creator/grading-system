@extends('layouts.admin')

@section('title', 'Forbes Academy | User Management')
@section('header', 'User Management')
@section('header-description', 'Manage student, teacher, adviser, and administrator accounts.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/user-management.css') }}">
@endsection

@section('content')
    <div class="user-management-container">

        {{-- =========================
        ACCOUNT STATISTICS
        ========================== --}}
        <div class="user-stats-grid">

            <div class="user-stat-card">
                <div class="user-stat-icon total"><i class="fas fa-users"></i></div>
                <div class="user-stat-info">
                    <span>Total Accounts</span>
                    <h3 id="totalUsers">0</h3>
                </div>
            </div>

            <div class="user-stat-card">
                <div class="user-stat-icon student"><i class="fas fa-user-graduate"></i></div>
                <div class="user-stat-info">
                    <span>Student Accounts</span>
                    <h3 id="totalStudents">0</h3>
                </div>
            </div>

            <div class="user-stat-card">
                <div class="user-stat-icon teacher"><i class="fas fa-chalkboard-teacher"></i></div>
                <div class="user-stat-info">
                    <span>Teacher Accounts</span>
                    <h3 id="totalTeachers">0</h3>
                </div>
            </div>

            <div class="user-stat-card">
                <div class="user-stat-icon adviser"><i class="fas fa-user-tie"></i></div>
                <div class="user-stat-info">
                    <span>Adviser Accounts</span>
                    <h3 id="totalAdvisers">0</h3>
                </div>
            </div>

            <div class="user-stat-card">
                <div class="user-stat-icon admin"><i class="fas fa-user-shield"></i></div>
                <div class="user-stat-info">
                    <span>Admin Accounts</span>
                    <h3 id="totalAdmins">0</h3>
                </div>
            </div>

            <div class="user-stat-card">
                <div class="user-stat-icon active"><i class="fas fa-check-circle"></i></div>
                <div class="user-stat-info">
                    <span>Active Accounts</span>
                    <h3 id="totalActive">0</h3>
                </div>
            </div>

            <div class="user-stat-card">
                <div class="user-stat-icon pending"><i class="fas fa-clock"></i></div>
                <div class="user-stat-info">
                    <span>Pending Accounts</span>
                    <h3 id="totalPending">0</h3>
                </div>
            </div>

            <div class="user-stat-card">
                <div class="user-stat-icon inactive"><i class="fas fa-ban"></i></div>
                <div class="user-stat-info">
                    <span>Inactive Accounts</span>
                    <h3 id="totalInactive">0</h3>
                </div>
            </div>

        </div>

        {{-- =========================
        USER TABLE
        ========================== --}}
        <div class="user-content-card">

            <div class="user-content-header">
                <div>
                    <h3>System Accounts</h3>
                    <p>View and manage the accounts registered in the system.</p>
                </div>

                <button type="button" class="btn-add-user" id="openAddUser">
                    <i class="fas fa-plus"></i>
                    Add User
                </button>
            </div>

            <div class="user-toolbar">
                <div class="user-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search name, username, or email...">
                </div>

                <div class="user-filters">
                    <select id="roleFilter">
                        <option value="all">All Roles</option>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                        <option value="adviser">Adviser</option>
                        <option value="teacher_adviser">Teacher + Adviser</option>
                        <option value="admin">Administrator</option>
                    </select>

                    <select id="statusFilter">
                        <option value="all">All Account Statuses</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="user-table-wrapper">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Email Address</th>
                            <th>Role</th>
                            <th>Account Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody id="userTableBody">
                        <tr id="emptyStateRow">
                            <td colspan="6" class="empty-state">
                                <i class="fas fa-users"></i>
                                <h4>No Accounts Found</h4>
                                <p>Accounts will appear here once they have been created.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="user-table-footer">
                <span>Account status is managed separately from Student and Faculty profile status.</span>
            </div>

        </div>
    </div>



    {{-- =====================================================
    ADD / EDIT USER MODAL
    ====================================================== --}}
    <div class="modal-overlay" id="userModal">
        <div class="modal-container user-account-modal">

            <form id="userForm">
                @csrf

                <input type="hidden" id="userId" name="user_id">

                {{-- MODAL HEADER --}}
                <div class="modal-header">
                    <div>
                        <h3 id="userModalTitle">Create New Account</h3>
                        <p id="userModalDescription">
                            Create a system account and its corresponding profile.
                        </p>
                    </div>

                    <button type="button" class="modal-close" id="closeUserModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- SCROLLABLE MODAL BODY --}}
                <div class="modal-body">

                    {{-- ACCOUNT TYPE --}}
                    <div class="form-section">
                        <div class="form-section-heading">
                            <h4>Account Type</h4>
                            <p>Select which type of profile you want to create.</p>
                        </div>

                        <div class="form-group">
                            <label for="profileType">
                                Profile Type <span class="required">*</span>
                            </label>

                            <select id="profileType" name="profile_type" required>
                                <option value="">Select profile type</option>
                                <option value="student">Student</option>
                                <option value="faculty">Faculty</option>
                            </select>
                        </div>
                    </div>

                    {{-- PERSONAL INFORMATION --}}
                    <div class="form-section">
                        <div class="form-section-heading">
                            <h4>Personal Information</h4>
                            <p>Enter the basic information of the account owner.</p>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">
                                    First Name <span class="required">*</span>
                                </label>
                                <input type="text" id="firstName" name="first_name" required>
                            </div>

                            <div class="form-group">
                                <label for="middleName">Middle Name</label>
                                <input type="text" id="middleName" name="middle_name">
                            </div>

                            <div class="form-group">
                                <label for="lastName">
                                    Last Name <span class="required">*</span>
                                </label>
                                <input type="text" id="lastName" name="last_name" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="Enter email address">
                            <small>
                                This email will be associated with the user's login account.
                            </small>
                        </div>
                    </div>

                    {{-- STUDENT PROFILE --}}
                    <div class="form-section conditional-form-section" id="studentFields" hidden>

                        <div class="form-section-heading">
                            <h4>Student Information</h4>
                            <p>Provide the student's academic profile information.</p>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="studentId">
                                    Student ID <span class="required">*</span>
                                </label>
                                <input type="text" id="studentId" name="student_id">
                            </div>

                            <div class="form-group">
                                <label for="studentSex">
                                    Sex <span class="required">*</span>
                                </label>
                                <select id="studentSex" name="sex">
                                    <option value="">Select sex</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="studentGradeLevel">
                                    Grade Level <span class="required">*</span>
                                </label>
                                <select id="studentGradeLevel" name="grade_level">
                                    <option value="">Select grade level</option>
                                    <option value="Grade 7">Grade 7</option>
                                    <option value="Grade 8">Grade 8</option>
                                    <option value="Grade 9">Grade 9</option>
                                    <option value="Grade 10">Grade 10</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="studentSection">Section</label>
                                <input type="text" id="studentSection" name="section"
                                    placeholder="Enter section (if applicable)">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="studentSchoolYear">
                                School Year <span class="required">*</span>
                            </label>

                            <select id="studentSchoolYear" name="school_year">
                                <option value="">Select school year</option>
                                <option value="2025-2026">2025-2026</option>
                                <option value="2026-2027">2026-2027</option>
                                <option value="2027-2028">2027-2028</option>
                                <option value="2028-2029">2028-2029</option>
                            </select>
                        </div>
                    </div>

                    {{-- FACULTY PROFILE --}}
                    <div class="form-section conditional-form-section" id="facultyFields" hidden>

                        <div class="form-section-heading">
                            <h4>Faculty Information</h4>
                            <p>Provide the faculty member's responsibilities and assignments.</p>
                        </div>

                        <div class="form-group">
                            <label for="facultyId">
                                Faculty ID <span class="required">*</span>
                            </label>
                            <input type="text" id="facultyId" name="faculty_id">
                        </div>

                        {{-- RESPONSIBILITIES --}}
                        <div class="form-group">
                            <label>
                                Faculty Responsibilities <span class="required">*</span>
                            </label>

                            <div class="responsibility-options">
                                <label class="responsibility-option">
                                    <input type="checkbox" id="isTeacher" name="is_teacher" value="1">

                                    <span>
                                        <strong>Teacher</strong>
                                        <small>
                                            Handles subjects and submits student grades.
                                        </small>
                                    </span>
                                </label>

                                <label class="responsibility-option">
                                    <input type="checkbox" id="isAdviser" name="is_adviser" value="1">

                                    <span>
                                        <strong>Adviser</strong>
                                        <small>
                                            Reviews and forwards assigned grade-level records.
                                        </small>
                                    </span>
                                </label>
                            </div>
                        </div>

                        {{-- TEACHING ASSIGNMENTS --}}
                        <div class="assignment-section" id="teachingAssignmentSection" hidden>

                            <div class="assignment-section-heading">
                                <div>
                                    <h5>Teaching Assignments</h5>
                                    <p>Add the grade levels and subjects assigned to this teacher.</p>
                                </div>

                                <button type="button" class="btn-add-assignment" id="addTeachingAssignment">
                                    <i class="fas fa-plus"></i> Add Assignment
                                </button>
                            </div>

                            <div id="teachingAssignmentsContainer"></div>
                        </div>

                        {{-- ADVISORY ASSIGNMENT --}}
                        <div class="assignment-section" id="advisoryAssignmentSection" hidden>

                            <div class="assignment-section-heading">
                                <div>
                                    <h5>Advisory Assignment</h5>
                                    <p>Assign one grade level and school year to this adviser.</p>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="advisoryGrade">
                                        Advisory Grade Level <span class="required">*</span>
                                    </label>

                                    <select id="advisoryGrade" name="advisory_grade">
                                        <option value="">Select grade level</option>
                                        <option value="Grade 7">Grade 7</option>
                                        <option value="Grade 8">Grade 8</option>
                                        <option value="Grade 9">Grade 9</option>
                                        <option value="Grade 10">Grade 10</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="advisorySchoolYear">
                                        School Year <span class="required">*</span>
                                    </label>

                                    <select id="advisorySchoolYear" name="advisory_school_year">
                                        <option value="">Select school year</option>
                                        <option value="2025-2026">2025-2026</option>
                                        <option value="2026-2027">2026-2027</option>
                                        <option value="2027-2028">2027-2028</option>
                                        <option value="2028-2029">2028-2029</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ACCOUNT SETUP NOTICE --}}
                    <div class="setup-notice">
                        <div class="setup-notice-icon">
                            <i class="fas fa-envelope"></i>
                        </div>

                        <div>
                            <strong>Account Setup Link</strong>
                            <p>
                                Newly created accounts will be marked as Pending.
                                The email setup link feature will be implemented in a future update.
                            </p>
                        </div>
                    </div>

                </div>

                {{-- MODAL FOOTER --}}
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" id="cancelUserModal">
                        Cancel
                    </button>

                    <button type="submit" class="btn-save-user" id="saveUserBtn">
                        <span id="saveUserText">Create Account</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

            </form>
        </div>
    </div>



    {{-- =====================================================
    VIEW USER DETAILS MODAL
    ====================================================== --}}
    <div class="modal-overlay" id="viewUserModal">
        <div class="modal-container view-user-modal">

            <div class="modal-header">
                <div>
                    <h3>User Details</h3>
                    <p>Account and profile information.</p>
                </div>

                <button type="button" class="modal-close" id="closeViewUserModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body">
                <div id="viewUserDetails"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="closeViewUserButton">Close</button>
            </div>

        </div>
    </div>


    {{-- =====================================================
    ACCOUNT STATUS CONFIRMATION MODAL
    ====================================================== --}}
    <div class="modal-overlay" id="deleteUserModal">
        <div class="modal-container confirmation-modal">

            <div class="modal-header">
                <div>
                    <h3 id="statusModalHeading">Disable Account?</h3>
                    <p>Confirm the account status change.</p>
                </div>

                <button type="button" class="modal-close" id="closeStatusModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="confirmation-icon" id="statusModalIcon">
                    <i class="fas fa-user-slash"></i>
                </div>

                <p id="statusModalDescription">
                    Are you sure you want to disable this account?
                </p>

                <strong id="deleteUserName"></strong>

                <p class="confirmation-warning" id="statusModalWarning">
                    The user will no longer be able to access the system while the account is inactive.
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="cancelStatusModal">Cancel</button>

                <button type="button" class="btn-confirm-delete" id="confirmDeleteUser">
                    <span id="confirmStatusText">Disable Account</span>
                </button>
            </div>

        </div>
    </div>


    {{-- =====================================================
    RESET PASSWORD MODAL
    ====================================================== --}}
    <div class="modal-overlay" id="resetPasswordModal">
        <div class="modal-container reset-password-modal">

            <div class="modal-header">
                <div>
                    <h3>Reset Password</h3>
                    <p>Set a new password for this account.</p>
                </div>

                <button type="button" class="modal-close" id="closeResetPasswordModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="resetPasswordForm">
                @csrf

                <div class="modal-body">
                    <p class="reset-password-user">
                        Resetting password for: <strong id="resetUserName"></strong>
                    </p>

                    <div class="form-group">
                        <label for="newPassword">New Password <span class="required">*</span></label>
                        <input type="password" id="newPassword" name="password" minlength="8" required
                            autocomplete="new-password">
                    </div>

                    <div class="form-group">
                        <label for="confirmNewPassword">Confirm New Password <span class="required">*</span></label>
                        <input type="password" id="confirmNewPassword" name="password_confirmation" minlength="8" required
                            autocomplete="new-password">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" id="cancelResetPassword">Cancel</button>

                    <button type="submit" class="btn-save-user" id="confirmResetPassword">
                        Reset Password
                    </button>
                </div>
            </form>

        </div>
    </div>

@endsection


@section('page-js')
    <script>
        window.userManagementRoutes = {
            data: @json(route('admin.users.data')),
            store: @json(route('admin.users.store')),
            update: @json(route('admin.users.update', ['user' => '__ID__'])),
            toggleStatus: @json(route('admin.users.toggle-status', ['user' => '__ID__'])),
            resetPassword: @json(route('admin.users.reset-password', ['user' => '__ID__']))
        };
    </script>

    <script src="{{ asset('js/admin/user-management.js') }}"></script>
@endsection