```blade
@extends('layouts.teacher')

@section('title', 'My Profile')

@section('header', 'My Profile')

@section('header-description', 'View and manage your personal and account information.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/teacher/teacher-profile.css') }}">
@endsection

@section('content')

    <div class="teacher-profile-container">

        
        {{-- PROFILE HEADER --}}
        <div class="profile-header-card">

            <div class="profile-avatar">
                <span>TH</span>
            </div>  

            <div class="profile-header-info">
                <h2>Teacher Hicap</h2>
                <p>Junior High School Teacher</p>
                <span class="profile-status">
                    <i class="fa-solid fa-circle"></i>
                    Active
                </span>
            </div>

            <div class="profile-header-actions">
                <button type="button" class="profile-btn primary" id="editProfileBtn">
                    <i class="fa-solid fa-pen"></i>
                    Edit Profile
                </button>

                <button type="button" class="profile-btn secondary" id="changePasswordBtn">
                    <i class="fa-solid fa-lock"></i>
                    Change Password
                </button>
            </div>

        </div>

        {{-- PROFILE CONTENT --}}
        <div class="profile-grid">

            {{-- PERSONAL INFORMATION --}}
            <div class="profile-card">

                <div class="profile-card-header">
                    <div class="profile-card-title">
                        <div class="profile-card-icon">
                            <i class="fa-solid fa-user"></i>
                        </div>

                        <div>
                            <h3>Personal Information</h3>
                            <p>Your basic personal information</p>
                        </div>
                    </div>
                </div>

                <div class="profile-info-grid">

                    <div class="profile-info-item">
                        <span class="info-label">Full Name</span>
                        <span class="info-value">Teacher Harvy</span>
                    </div>

                    <div class="profile-info-item">
                        <span class="info-label">Teacher ID</span>
                        <span class="info-value">TCH-2026-001</span>
                    </div>

                    <div class="profile-info-item">
                        <span class="info-label">Email Address</span>
                        <span class="info-value">teacher@forbesacademy.edu.ph</span>
                    </div>

                    <div class="profile-info-item">
                        <span class="info-label">Contact Number</span>
                        <span class="info-value">0921 847 0126</span>
                    </div>

                    <div class="profile-info-item">
                        <span class="info-label">Department</span>
                        <span class="info-value">Junior High School</span>
                    </div>

                    <div class="profile-info-item">
                        <span class="info-label">Position</span>
                        <span class="info-value">Teacher</span>
                    </div>

                </div>

            </div>

            {{-- ACCOUNT INFORMATION --}}
            <div class="profile-card">

                <div class="profile-card-header">
                    <div class="profile-card-title">
                        <div class="profile-card-icon account">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>

                        <div>
                            <h3>Account Information</h3>
                            <p>Your system account details</p>
                        </div>
                    </div>
                </div>

                <div class="profile-info-list">

                    <div class="profile-list-item">
                        <div class="list-item-icon">
                            <i class="fa-solid fa-user-tag"></i>
                        </div>

                        <div class="list-item-content">
                            <span class="info-label">Account Role</span>
                            <span class="info-value">Teacher</span>
                        </div>
                    </div>

                    <div class="profile-list-item">
                        <div class="list-item-icon">
                            <i class="fa-solid fa-calendar"></i>
                        </div>

                        <div class="list-item-content">
                            <span class="info-label">School Year</span>
                            <span class="info-value">2026-2027</span>
                        </div>
                    </div>

                    <div class="profile-list-item">
                        <div class="list-item-icon">
                            <i class="fa-solid fa-clock"></i>
                        </div>

                        <div class="list-item-content">
                            <span class="info-label">Account Status</span>
                            <span class="account-active">Active</span>
                        </div>
                    </div>

                    <div class="profile-list-item">
                        <div class="list-item-icon">
                            <i class="fa-solid fa-right-to-bracket"></i>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- TEACHING INFORMATION --}}
        <div class="profile-card teaching-card">

            <div class="profile-card-header">
                <div class="profile-card-title">
                    <div class="profile-card-icon teaching">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>

                    <div>
                        <h3>Teaching Information</h3>
                        <p>Your current teaching assignments</p>
                    </div>
                </div>
            </div>

            <div class="teaching-info-grid">

                <div class="teaching-info-item">
                    <span class="teaching-number">3</span>
                    <span class="teaching-label">Assigned Classes</span>
                </div>

                <div class="teaching-info-item">
                    <span class="teaching-number">5</span>
                    <span class="teaching-label">Subjects</span>
                </div>

                <div class="teaching-info-item">
                    <span class="teaching-number">96</span>
                    <span class="teaching-label">Students</span>
                </div>

                <div class="teaching-info-item">
                    <span class="teaching-number">1st</span>
                    <span class="teaching-label">Current Term</span>
                </div>

            </div>

        </div>

    </div>


    {{-- EDIT PROFILE MODAL --}}
    <div class="profile-modal-overlay" id="editProfileModal">

        <div class="profile-modal">

            <div class="modal-header">
                <div>
                    <h3>Edit Profile</h3>
                    <p>Update your personal information.</p>
                </div>

                <button type="button" class="modal-close" id="closeEditProfile">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="editProfileForm">

                <div class="modal-form-grid">

                    <div class="form-group">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" value="Teacher Cruz">
                    </div>

                    <div class="form-group">
                        <label for="teacherId">Teacher ID</label>
                        <input type="text" id="teacherId" value="TCH-2026-001" disabled>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" value="teacher@forbesacademy.edu.ph">
                    </div>

                    <div class="form-group">
                        <label for="contact">Contact Number</label>
                        <input type="text" id="contact" value="0912 345 6789">
                    </div>

                </div>

                <div class="modal-actions">

                    <button type="button" class="modal-btn cancel" id="cancelEditProfile">
                        Cancel
                    </button>

                    <button type="submit" class="modal-btn save">
                        <i class="fa-solid fa-check"></i>
                        Save Changes
                    </button>

                </div>

            </form>

        </div>

    </div>


    {{-- CHANGE PASSWORD MODAL --}}
    <div class="profile-modal-overlay" id="changePasswordModal">

        <div class="profile-modal password-modal">

            <div class="modal-header">

                <div>
                    <h3>Change Password</h3>
                    <p>Update your account password.</p>
                </div>

                <button type="button" class="modal-close" id="closeChangePassword">
                    <i class="fa-solid fa-xmark"></i>
                </button>

            </div>

            <form id="changePasswordForm">

                <div class="form-group">
                    <label for="currentPassword">Current Password</label>

                    <div class="password-input">
                        <input type="password" id="currentPassword">

                        <button type="button" class="toggle-password" data-target="currentPassword">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="newPassword">New Password</label>

                    <div class="password-input">
                        <input type="password" id="newPassword">

                        <button type="button" class="toggle-password" data-target="newPassword">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>

                    <div class="password-input">
                        <input type="password" id="confirmPassword">

                        <button type="button" class="toggle-password" data-target="confirmPassword">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="password-note">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Use a strong password that you do not use on other accounts.</span>
                </div>

                <div class="modal-actions">

                    <button type="button" class="modal-btn cancel" id="cancelChangePassword">
                        Cancel
                    </button>

                    <button type="submit" class="modal-btn save">
                        <i class="fa-solid fa-key"></i>
                        Update Password
                    </button>

                </div>

            </form>

        </div>

    </div>

@endsection


@section('page-js')
    <script src="{{ asset('js/teacher/teacher-profile.js') }}"></script>
@endsection
```

