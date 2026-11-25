{{-- EXTENDS TEACHER LAYOUT --}}
@extends('layouts.teacher')

@section('title', 'My Profile')
@section('header', 'My Profile')
@section('header-description', 'View and manage your personal and account information.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/teacher/teacher-profile.css') }}">
@endsection

@section('content')

    @php
        // Information provided by TeacherProfileController.
        $profileUser = $user;
        $profileName = $profileUser->name ?? 'Name not available';
        $profileEmail = $profileUser->email ?? 'Email not provided';

        // Actual Teacher ID comes from the linked Faculty record.
        $teacherId = $faculty->faculty_id ?? null;
        $displayTeacherId = $teacherId ?: 'Not assigned';

        // Account information.
        $accountRole = $profileUser->role ?? 'Teacher';
        $accountStatus = $profileUser->account_status ?? 'Not available';

        // Generate initials from the user's name.
        $initials = collect(explode(' ', trim($profileName)))
            ->filter()
            ->take(2)
            ->map(fn($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');

        // Teaching information.
        $assignedClassCount = $assignedClasses->count();
        $subjectCount = $subjects->count();

        // Display unique assigned grade levels and subjects.
        $displayClasses = $assignedClassCount
            ? $assignedClasses->map(fn($grade) => 'Grade ' . $grade)->implode(', ')
            : 'No assigned classes';

        $displaySubjects = $subjectCount
            ? $subjects->implode(', ')
            : 'No assigned subjects';
    @endphp

    <div class="teacher-profile-container">

        {{-- PROFILE HEADER --}}
        <section class="profile-header-card">

            <div class="profile-avatar-wrapper">

                <div class="profile-avatar" id="teacherProfileAvatar">

                    @if (!empty($profileUser->profile_photo_path))
                        <img src="{{ asset('storage/' . $profileUser->profile_photo_path) }}" alt="Teacher Profile Picture"
                            id="teacherProfileImage">
                    @else
                        <span id="teacherProfileInitials">{{ $initials ?: 'T' }}</span>
                    @endif

                </div>

                <button type="button" class="profile-photo-upload-btn" id="openTeacherPhotoModal"
                    aria-label="Change profile picture">
                    <i class="fa-solid fa-camera"></i>
                </button>

            </div>

            <div class="profile-header-info">
                <h2>{{ $profileName }}</h2>
                <p>Junior High School Teacher</p>

                <span class="profile-status">
                    <i class="fa-solid fa-circle" aria-hidden="true"></i>
                    {{ ucfirst($accountStatus) }}
                </span>
            </div>

            <div class="profile-header-actions">
                <button type="button" class="profile-btn primary" id="editProfileBtn">
                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                    Edit Profile
                </button>

                <button type="button" class="profile-btn secondary" id="changePasswordBtn">
                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                    Change Password
                </button>
            </div>

        </section>

        {{-- PROFILE CONTENT --}}
        <div class="profile-grid">

            {{-- PERSONAL INFORMATION --}}
            <section class="profile-card">

                <div class="profile-card-header">
                    <div class="profile-card-title">

                        <div class="profile-card-icon">
                            <i class="fa-solid fa-user" aria-hidden="true"></i>
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
                        <span class="info-value">{{ $profileName }}</span>
                    </div>

                    <div class="profile-info-item">
                        <span class="info-label">Teacher ID</span>
                        <span class="info-value">{{ $displayTeacherId }}</span>
                    </div>

                    <div class="profile-info-item">
                        <span class="info-label">Email Address</span>
                        <span class="info-value">{{ $profileEmail }}</span>
                    </div>

                    <div class="profile-info-item">
                        <span class="info-label">Department</span>
                        <span class="info-value">Junior High School</span>
                    </div>

                    <div class="profile-info-item">
                        <span class="info-label">Position</span>
                        <span class="info-value">{{ ucfirst($faculty->role ?? $accountRole) }}</span>
                    </div>

                </div>

            </section>

            {{-- ACCOUNT INFORMATION --}}
            <section class="profile-card">

                <div class="profile-card-header">
                    <div class="profile-card-title">

                        <div class="profile-card-icon account">
                            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
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
                            <i class="fa-solid fa-user-tag" aria-hidden="true"></i>
                        </div>

                        <div class="list-item-content">
                            <span class="info-label">Account Role</span>
                            <span class="info-value">{{ ucfirst($accountRole) }}</span>
                        </div>
                    </div>

                    <div class="profile-list-item">
                        <div class="list-item-icon">
                            <i class="fa-solid fa-calendar" aria-hidden="true"></i>
                        </div>

                        <div class="list-item-content">
                            <span class="info-label">School Year</span>
                            <span class="info-value">2026-2027</span>
                        </div>
                    </div>

                    <div class="profile-list-item">
                        <div class="list-item-icon">
                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                        </div>

                        <div class="list-item-content">
                            <span class="info-label">Account Status</span>
                            <span class="info-value">{{ ucfirst($accountStatus) }}</span>
                        </div>
                    </div>

                </div>

            </section>

        </div>

        {{-- TEACHING INFORMATION --}}
        <section class="profile-card teaching-card">

            <div class="profile-card-header">
                <div class="profile-card-title">

                    <div class="profile-card-icon teaching">
                        <i class="fa-solid fa-chalkboard-user" aria-hidden="true"></i>
                    </div>

                    <div>
                        <h3>Teaching Information</h3>
                        <p>Your current teaching assignments</p>
                    </div>

                </div>
            </div>

            <div class="teaching-info-grid">

                <div class="teaching-info-item">
                    <span class="teaching-number">{{ $assignedClassCount }}</span>
                    <span class="teaching-label">Assigned Classes</span>
                    <small>{{ $displayClasses }}</small>
                </div>

                <div class="teaching-info-item">
                    <span class="teaching-number">{{ $subjectCount }}</span>
                    <span class="teaching-label">Subjects</span>
                    <small>{{ $displaySubjects }}</small>
                </div>

                <div class="teaching-info-item">
                    <span class="teaching-number">{{ $studentCount }}</span>
                    <span class="teaching-label">Students</span>
                </div>

                <div class="teaching-info-item">
                    <span class="teaching-number">1st</span>
                    <span class="teaching-label">Current Term</span>
                </div>

            </div>

        </section>

    </div>

    {{-- PROFILE PHOTO MODAL --}}
    <div class="profile-modal-overlay" id="teacherPhotoModal" aria-hidden="true">

        <section class="profile-modal photo-modal" role="dialog" aria-modal="true" aria-labelledby="teacherPhotoTitle">

            <div class="modal-header">
                <div>
                    <h3 id="teacherPhotoTitle">Profile Picture</h3>
                    <p>Upload a photo for your teacher account.</p>
                </div>

                <button type="button" class="modal-close" id="closeTeacherPhotoModal"
                    aria-label="Close profile picture modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="teacherPhotoForm" action="{{ route('teacher.profile.photo.update') }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                <div class="photo-upload-content">

                    <div class="photo-preview-wrapper">
                        <img src="{{ !empty($profileUser->profile_photo_path)
        ? asset('storage/' . $profileUser->profile_photo_path)
        : '' }}" alt="Profile Picture Preview" id="teacherPhotoPreview"
                            class="{{ empty($profileUser->profile_photo_path) ? 'is-hidden' : '' }}">

                        <div id="teacherPhotoPlaceholder"
                            class="{{ !empty($profileUser->profile_photo_path) ? 'is-hidden' : '' }}">
                            <i class="fa-solid fa-user"></i>
                        </div>
                    </div>

                    <label for="teacherPhotoInput" class="photo-upload-label">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        Choose Photo
                    </label>

                    <input type="file" id="teacherPhotoInput" name="profile_photo" accept=".jpg,.jpeg,.png,.webp" hidden>

                    <p class="photo-upload-hint">
                        JPG, JPEG, PNG, or WEBP. Maximum file size: 2 MB.
                    </p>

                </div>

                <div class="modal-actions">
                    <button type="button" class="modal-btn cancel" id="cancelTeacherPhoto">
                        Cancel
                    </button>

                    <button type="submit" class="modal-btn save" id="saveTeacherPhoto">
                        <i class="fa-solid fa-check"></i>
                        Save Picture
                    </button>
                </div>

            </form>

        </section>

    </div>

    {{-- EDIT PROFILE MODAL --}}
    <div class="profile-modal-overlay" id="editProfileModal" aria-hidden="true">

        <section class="profile-modal" role="dialog" aria-modal="true" aria-labelledby="editProfileTitle">

            <div class="modal-header">
                <div>
                    <h3 id="editProfileTitle">Edit Profile</h3>
                    <p>Update your personal information.</p>
                </div>

                <button type="button" class="modal-close" id="closeEditProfile" aria-label="Close edit profile">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>

            <form id="editProfileForm">

                <div class="modal-form-grid">

                    <div class="form-group">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" name="name" value="{{ $profileName }}" maxlength="255"
                            autocomplete="name" required>
                    </div>

                    <div class="form-group">
                        <label for="teacherId">Teacher ID</label>
                        <input type="text" id="teacherId" value="{{ $displayTeacherId }}" disabled>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ $profileEmail }}" maxlength="255"
                            autocomplete="email" required>
                    </div>

                </div>

                <p class="profile-form-note">
                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                    Changes will be saved after the profile form is connected
                    to the Laravel backend.
                </p>

                <div class="modal-actions">
                    <button type="button" class="modal-btn cancel" id="cancelEditProfile">
                        Cancel
                    </button>

                    <button type="submit" class="modal-btn save">
                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                        Save Changes
                    </button>
                </div>

            </form>

        </section>

    </div>

    {{-- CHANGE PASSWORD MODAL --}}
    <div class="profile-modal-overlay" id="changePasswordModal" aria-hidden="true">

        <section class="profile-modal password-modal" role="dialog" aria-modal="true" aria-labelledby="changePasswordTitle">

            <div class="modal-header">

                <div>
                    <h3 id="changePasswordTitle">Change Password</h3>
                    <p>Update your account password.</p>
                </div>

                <button type="button" class="modal-close" id="closeChangePassword" aria-label="Close change password">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>

            </div>

            <form id="changePasswordForm">

                <div class="form-group">
                    <label for="currentPassword">Current Password</label>

                    <div class="password-input">
                        <input type="password" id="currentPassword" name="current_password" autocomplete="current-password"
                            required>

                        <button type="button" class="toggle-password" data-target="currentPassword"
                            aria-label="Show current password">
                            <i class="fa-solid fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="newPassword">New Password</label>

                    <div class="password-input">
                        <input type="password" id="newPassword" name="password" autocomplete="new-password" minlength="8"
                            required>

                        <button type="button" class="toggle-password" data-target="newPassword"
                            aria-label="Show new password">
                            <i class="fa-solid fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>

                    <div class="password-input">
                        <input type="password" id="confirmPassword" name="password_confirmation" autocomplete="new-password"
                            minlength="8" required>

                        <button type="button" class="toggle-password" data-target="confirmPassword"
                            aria-label="Show password confirmation">
                            <i class="fa-solid fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                <div class="password-note">
                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                    <span>
                        Use a strong password of at least 8 characters.
                        Your current password will need to be verified.
                    </span>
                </div>

                <div class="modal-actions">
                    <button type="button" class="modal-btn cancel" id="cancelChangePassword">
                        Cancel
                    </button>

                    <button type="submit" class="modal-btn save">
                        <i class="fa-solid fa-key" aria-hidden="true"></i>
                        Update Password
                    </button>
                </div>

            </form>

        </section>

    </div>

    {{-- FEEDBACK MESSAGE --}}
    <div class="profile-toast" id="profileToast" role="status" aria-live="polite"></div>

@endsection

@section('page-js')
    <script src="{{ asset('js/teacher/teacher-profile.js') }}"></script>
@endsection