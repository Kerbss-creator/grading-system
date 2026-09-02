@extends('layouts.admin')

@section('title', 'Settings')

@section('header', 'Settings')

@section('header-description', 'Configure system preferences, account details, and administrative settings.')

@section('page-css')

  <link rel="stylesheet" href="{{ asset('css/settings-admin.css') }}">
@endsection

@section('content')

  <!-- SETTINGS CONTAINER -->

  <section class="settings-container">


    <!-- SETTINGS SIDEBAR -->

    <div class="settings-sidebar">

      <button class="settings-tab active" data-section="general">
        <i class="fa-solid fa-sliders"></i>
        <span>General Settings</span>
      </button>

      <button class="settings-tab" data-section="grades">
        <i class="fa-solid fa-graduation-cap"></i>
        <span>Grade Settings</span>
      </button>

      <button class="settings-tab" data-section="account">
        <i class="fa-solid fa-user-gear"></i>
        <span>Account Settings</span>
      </button>

      <button class="settings-tab" data-section="notifications">
        <i class="fa-solid fa-bell"></i>
        <span>Notifications</span>
      </button>

      <button class="settings-tab" data-section="data">
        <i class="fa-solid fa-database"></i>
        <span>Data Management</span>
      </button>

    </div>

    <!-- SETTINGS CONTENT -->

    <div class="settings-content">

      <!-- GENERAL SETTINGS -->

      <div class="settings-section active" id="general">

        <div class="settings-section-header">

          <div>
            <h2>General Settings</h2>
            <p>Manage basic information about the grading system.</p>
          </div>

        </div>

        <div class="settings-form">

          <div class="form-group">

            <label for="schoolName">
              School Name
            </label>

            <input type="text" id="schoolName" value="Forbes Academy">

          </div>

          <div class="form-row">

            <div class="form-group">

              <label for="schoolYear">
                School Year
              </label>

              <select id="schoolYear">

                <option>2025 - 2026</option>

                <option selected>
                  2026 - 2027
                </option>

                <option>2027 - 2028</option>

                <option>2028 - 2029</option>

                <option>2030 - 2031</option>

              </select>

            </div>

            <div class="form-group">

              <label for="semester">
                Current Semester
              </label>

              <select id="semester">

                <option selected>
                  First Semester
                </option>

                <option>
                  Second Semester
                </option>

                <option>
                  Third Semester
                </option>

              </select>

            </div>

          </div>

          <div class="form-group">

            <label>
              System Status
            </label>

            <div class="status-box">

              <div class="status-indicator">

                <span></span>

                <div>

                  <strong>
                    System Active
                  </strong>

                  <p>
                    The grading system is currently operational.
                  </p>

                </div>

              </div>

            </div>

          </div>

        </div>

      </div>

      <!-- GRADE SETTINGS -->

      <div class="settings-section" id="grades">

        <div class="settings-section-header">

          <div>

            <h2>Grade Settings</h2>

            <p>
              Configure grading rules used by the system.
            </p>

          </div>

        </div>

        <div class="settings-form">

          <div class="form-row">

            <div class="form-group">

              <label for="passingGrade">
                Passing Grade
              </label>

              <input type="number" id="passingGrade" value="75" min="0" max="100">

              <small>
                Students with grades below this value are considered failed.
              </small>

            </div>

            <div class="form-group">

              <label for="maximumGrade">
                Maximum Grade
              </label>

              <input type="number" id="maximumGrade" value="100" min="1" max="100">

            </div>

          </div>

          <div class="grade-remarks">

            <h3>
              Grade Remarks
            </h3>

            <div class="remark-row">

              <div>

                <strong>
                  75 - 100
                </strong>

                <span>
                  Passing
                </span>

              </div>

              <span class="remark-badge passed">
                Passed
              </span>

            </div>

            <div class="remark-row">

              <div>

                <strong>
                  Below 75
                </strong>

                <span>
                  Failing
                </span>

              </div>

              <span class="remark-badge failed">
                Failed
              </span>

            </div>

          </div>

        </div>

      </div>

      <!-- ACCOUNT SETTINGS -->

      <div class="settings-section" id="account">

        <div class="settings-section-header">

          <div>

            <h2>
              Account Settings
            </h2>

            <p>
              Manage your administrator account information.
            </p>

          </div>

        </div>

        <div class="settings-form">

          <div class="form-group">

            <label for="adminName">
              Administrator Name
            </label>

            <input type="text" id="adminName" value="Administrator">

          </div>

          <div class="form-group">

            <label for="adminEmail">
              Email Address
            </label>

            <input type="email" id="adminEmail" value="admin@forbesacademy.edu">

          </div>

          <div class="password-section">

            <div>

              <strong>
                Password
              </strong>

              <p>
                Change your administrator account password.
              </p>

            </div>

            <button class="secondary-btn" id="changePasswordBtn" type="button">

              <i class="fa-solid fa-key"></i>

              Change Password

            </button>

          </div>

        </div>

      </div>

      <!-- NOTIFICATION SETTINGS -->

      <div class="settings-section" id="notifications">

        <div class="settings-section-header">

          <div>

            <h2>
              Notification Settings
            </h2>

            <p>
              Control which system notifications are enabled.
            </p>

          </div>

        </div>

        <div class="notification-settings">

          <div class="setting-item">

            <div class="setting-info">

              <strong>
                Announcement Notifications
              </strong>

              <p>
                Receive notifications when announcements are created.
              </p>

            </div>

            <label class="switch">

              <input type="checkbox" checked>

              <span class="slider"></span>

            </label>

          </div>

          <div class="setting-item">

            <div class="setting-info">

              <strong>
                Grade Approval Notifications
              </strong>

              <p>
                Receive notifications when teachers submit grades.
              </p>

            </div>

            <label class="switch">

              <input type="checkbox" checked>

              <span class="slider"></span>

            </label>

          </div>

          <div class="setting-item">

            <div class="setting-info">

              <strong>
                System Notifications
              </strong>

              <p>
                Receive important system-related notifications.
              </p>

            </div>

            <label class="switch">

              <input type="checkbox" checked>

              <span class="slider"></span>

            </label>

          </div>

        </div>

      </div>

      <!-- DATA MANAGEMENT -->

      <div class="settings-section" id="data">

        <div class="settings-section-header">

          <div>

            <h2>
              Data Management
            </h2>

            <p>
              Manage academic records and system data.
            </p>

          </div>

        </div>

        <div class="data-management">

          <!-- EXPORT -->

          <div class="data-card">

            <div class="data-icon">

              <i class="fa-solid fa-file-excel"></i>

            </div>

            <div class="data-info">

              <h3>
                Export Records
              </h3>

              <p>
                Export student academic records to an Excel file.
              </p>

            </div>

            <button class="secondary-btn" type="button">

              <i class="fa-solid fa-download"></i>

              Export

            </button>

          </div>

          <!-- IMPORT -->

          <div class="data-card">

            <div class="data-icon">

              <i class="fa-solid fa-file-import"></i>

            </div>

            <div class="data-info">

              <h3>
                Import Records
              </h3>

              <p>
                Import academic records using an Excel file.
              </p>

            </div>

            <button class="secondary-btn" type="button">

              <i class="fa-solid fa-upload"></i>

              Import

            </button>

          </div>

          <!-- BACKUP -->

          <div class="data-card">

            <div class="data-icon">

              <i class="fa-solid fa-database"></i>

            </div>

            <div class="data-info">

              <h3>
                Backup Database
              </h3>

              <p>
                Create a backup of the current system data.
              </p>

            </div>

            <button class="secondary-btn" id="backupBtn" type="button">

              <i class="fa-solid fa-cloud-arrow-down"></i>

              Backup

            </button>

          </div>

        </div>

      </div>

      <!-- SETTINGS FOOTER -->

      <div class="settings-footer">

        <button class="cancel-settings" id="cancelSettings" type="button">
          Cancel
        </button>

        <button class="save-settings" id="saveSettings" type="button">

          <i class="fa-solid fa-check"></i>

          Save Changes

        </button>

      </div>

    </div>

  </section>

  <!-- CHANGE PASSWORD MODAL -->

  <div class="settings-modal" id="passwordModal">

    <div class="settings-modal-box">

      <div class="settings-modal-header">

        <div>

          <h2>
            Change Password
          </h2>

          <p>
            Update your administrator account password.
          </p>

        </div>

        <button class="close-modal" data-close="passwordModal" type="button">

          <i class="fa-solid fa-xmark"></i>

        </button>

      </div>

      <div class="modal-form">

        <div class="form-group">

          <label for="currentPassword">
            Current Password
          </label>

          <input type="password" id="currentPassword" placeholder="Enter current password">

        </div>

        <div class="form-group">

          <label for="newPassword">
            New Password
          </label>

          <input type="password" id="newPassword" placeholder="Enter new password">

        </div>

        <div class="form-group">

          <label for="confirmPassword">
            Confirm New Password
          </label>

          <input type="password" id="confirmPassword" placeholder="Confirm new password">

        </div>

      </div>

      <div class="modal-actions">

        <button class="cancel-btn close-modal" data-close="passwordModal" type="button">
          Cancel
        </button>

        <button class="save-btn" id="updatePasswordBtn" type="button">

          <i class="fa-solid fa-check"></i>

          Update Password

        </button>

      </div>

    </div>

  </div>

  <!-- BACKUP MODAL -->

  <div class="settings-modal" id="backupModal">

    <div class="settings-modal-box small-modal">

      <div class="backup-icon">

        <i class="fa-solid fa-database"></i>

      </div>

      <h2>
        Create Database Backup?
      </h2>

      <p>
        A backup copy of the current academic records and system data
        will be created.
      </p>

      <div class="modal-actions">

        <button class="cancel-btn close-modal" data-close="backupModal" type="button">
          Cancel
        </button>

        <button class="save-btn" id="confirmBackupBtn" type="button">

          <i class="fa-solid fa-download"></i>

          Create Backup

        </button>

      </div>

    </div>

  </div>

@endsection

@section('page-js')

  <script src="{{ asset('js/settings-admin.js') }}"></script>

@endsection