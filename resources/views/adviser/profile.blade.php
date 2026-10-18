{{-- EXTENDS ADVISER LAYOUT --}}
@extends('layouts.adviser')

{{-- PAGE TITLE --}}
@section('title', 'My Profile')

{{-- PAGE HEADER --}}
@section('header', 'My Profile')

{{-- PAGE HEADER DESCRIPTION --}}
@section('header-description', 'View and manage your personal and account information.')

{{-- PAGE CSS --}}
@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/adviser/adviser-profile.css') }}">
@endsection

{{-- PAGE CONTENT --}}
@section('content')
    @php
        $profileUser = auth()->user();
        $profileName = $profileUser->name ?? 'Adviser Name';
        $profileEmail = $profileUser->email ?? 'adviser@forbesacademy.edu.ph';
        $initials = collect(explode(' ', trim($profileName)))
            ->filter()
            ->take(2)
            ->map(fn($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');
    @endphp

    <div class="adviser-profile-container">

        {{-- PROFILE HEADER --}}
        <section class="adviser-profile-header">
            <div class="adviser-profile-avatar" aria-label="Profile initials">
                {{ $initials ?: 'AD' }}
            </div>

            <div class="adviser-profile-heading">
                <h2>{{ $profileName }}</h2>
                <p>Junior High School Adviser</p>
               
            </div>

            <div class="adviser-profile-actions">
                <button type="button" class="adviser-profile-btn primary" id="editProfileBtn">
                    <i class="fa-solid fa-pen"></i>
                    Edit Profile
                </button>
                <button type="button" class="adviser-profile-btn secondary" id="changePasswordBtn">
                    <i class="fa-solid fa-lock"></i>
                    Change Password
                </button>
            </div>
        </section>

        {{-- PROFILE INFORMATION --}}
        <div class="adviser-profile-grid">

            {{-- PERSONAL INFORMATION --}}
            <section class="adviser-profile-card">
                <div class="adviser-profile-card-header">
                    <div class="adviser-profile-card-icon">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                        <h3>Personal Information</h3>
                        <p>Your basic personal information</p>
                    </div>
                </div>

                <div class="adviser-profile-info-grid">
                    <div class="adviser-profile-info-item">
                        <span class="adviser-info-label">Full Name</span>
                        <span class="adviser-info-value">{{ $profileName }}</span>
                    </div>
                    <div class="adviser-profile-info-item">
                        <span class="adviser-info-label">Adviser ID</span>
                        <span class="adviser-info-value">{{ $profileUser->teacher_id ?? 'Not assigned' }}</span>
                    </div>
                    <div class="adviser-profile-info-item">
                        <span class="adviser-info-label">Email Address</span>
                        <span class="adviser-info-value">{{ $profileEmail }}</span>
                    </div>
                    <div class="adviser-profile-info-item">
                        <span class="adviser-info-label">Contact Number</span>
                        <span class="adviser-info-value">{{ $profileUser->contact_number ?? 'Not provided' }}</span>
                    </div>
                    <div class="adviser-profile-info-item">
                        <span class="adviser-info-label">Department</span>
                        <span class="adviser-info-value">Junior High School</span>
                    </div>
                    <div class="adviser-profile-info-item">
                        <span class="adviser-info-label">Position</span>
                        <span class="adviser-info-value">Adviser</span>
                    </div>
                </div>
            </section>

            {{-- ACCOUNT INFORMATION --}}
            <section class="adviser-profile-card">
                <div class="adviser-profile-card-header">
                    <div class="adviser-profile-card-icon account">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h3>Account Information</h3>
                        <p>Your system account details</p>
                    </div>
                </div>

                <div class="adviser-profile-info-list">
                    <div class="adviser-profile-list-item">
                        <span class="adviser-list-icon"><i class="fa-solid fa-user-tag"></i></span>
                        <div class="adviser-list-content">
                            <span class="adviser-info-label">Account Role</span>
                            <span class="adviser-info-value">Adviser</span>
                        </div>
                    </div>
                    <div class="adviser-profile-list-item">
                        <span class="adviser-list-icon"><i class="fa-solid fa-calendar"></i></span>
                        <div class="adviser-list-content">
                            <span class="adviser-info-label">School Year</span>
                            <span class="adviser-info-value">2026–2027</span>
                        </div>
                    </div>
                    <div class="adviser-profile-list-item">
                        <span class="adviser-list-icon"><i class="fa-solid fa-circle-check"></i></span>
                        <div class="adviser-list-content">
                            <span class="adviser-info-label">Account Status</span>
                            <span class="adviser-account-active">Active</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- ADVISORY INFORMATION --}}
        <section class="adviser-profile-card adviser-assignment-card">
            <div class="adviser-profile-card-header">
                <div class="adviser-profile-card-icon teaching">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <div>
                    <h3>Advisory Information</h3>
                    <p>Your current advisory assignment</p>
                </div>
            </div>

            <div class="adviser-assignment-grid">
                <div class="adviser-assignment-item">
                    <span class="adviser-assignment-value">Grade 7 - A</span>
                    <span class="adviser-assignment-label">Advisory Section</span>
                </div>
                <div class="adviser-assignment-item">
                    <span class="adviser-assignment-value">42</span>
                    <span class="adviser-assignment-label">Students in Section</span>
                </div>
                <div class="adviser-assignment-item">
                    <span class="adviser-assignment-value">1st Term</span>
                    <span class="adviser-assignment-label">Current Term</span>
                </div>
            </div>
        </section>
    </div>

    {{-- EDIT PROFILE MODAL --}}
    <div class="adviser-profile-modal-overlay" id="editProfileModal" aria-hidden="true">
        <section class="adviser-profile-modal" role="dialog" aria-modal="true" aria-labelledby="editProfileTitle">
            <div class="adviser-modal-header">
                <div>
                    <h3 id="editProfileTitle">Edit Profile</h3>
                    <p>Update your personal contact information.</p>
                </div>
                <button type="button" class="adviser-modal-close" id="closeEditProfile" aria-label="Close edit profile">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="editProfileForm">
                <div class="adviser-modal-form-grid">
                    <div class="adviser-form-group">
                        <label for="adviserFullName">Full Name</label>
                        <input type="text" id="adviserFullName" value="{{ $profileName }}" required>
                    </div>
                    <div class="adviser-form-group">
                        <label for="adviserId">Adviser ID</label>
                        <input type="text" id="adviserId" value="{{ $profileUser->teacher_id ?? 'ADV-2026-001' }}" disabled>
                    </div>
                    <div class="adviser-form-group">
                        <label for="adviserEmail">Email Address</label>
                        <input type="email" id="adviserEmail" value="{{ $profileEmail }}" required>
                    </div>
                    <div class="adviser-form-group">
                        <label for="adviserContact">Contact Number</label>
                        <input type="tel" id="adviserContact" value="{{ $profileUser->contact_number ?? '' }}"
                            placeholder="Enter contact number">
                    </div>
                </div>
                <p class="adviser-demo-note"><i class="fa-solid fa-circle-info"></i> This form is currently UI-only. Saving
                    changes to the database requires a Laravel route and controller.</p>
                <div class="adviser-modal-actions">
                    <button type="button" class="adviser-modal-btn cancel" id="cancelEditProfile">Cancel</button>
                    <button type="submit" class="adviser-modal-btn save">
                        <i class="fa-solid fa-check"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </section>
    </div>

    {{-- CHANGE PASSWORD MODAL --}}
    <div class="adviser-profile-modal-overlay" id="changePasswordModal" aria-hidden="true">
        <section class="adviser-profile-modal password-modal" role="dialog" aria-modal="true"
            aria-labelledby="changePasswordTitle">
            <div class="adviser-modal-header">
                <div>
                    <h3 id="changePasswordTitle">Change Password</h3>
                    <p>Choose a strong password for your account.</p>
                </div>
                <button type="button" class="adviser-modal-close" id="closeChangePassword"
                    aria-label="Close change password">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="changePasswordForm">
                <div class="adviser-form-group">
                    <label for="currentPassword">Current Password</label>
                    <div class="adviser-password-input">
                        <input type="password" id="currentPassword" autocomplete="current-password" required>
                        <button type="button" class="adviser-toggle-password" data-target="currentPassword"
                            aria-label="Show current password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="adviser-form-group">
                    <label for="newPassword">New Password</label>
                    <div class="adviser-password-input">
                        <input type="password" id="newPassword" autocomplete="new-password" minlength="8" required>
                        <button type="button" class="adviser-toggle-password" data-target="newPassword"
                            aria-label="Show new password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="adviser-form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <div class="adviser-password-input">
                        <input type="password" id="confirmPassword" autocomplete="new-password" minlength="8" required>
                        <button type="button" class="adviser-toggle-password" data-target="confirmPassword"
                            aria-label="Show confirmation password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
                <p class="adviser-password-note"><i class="fa-solid fa-circle-info"></i> Use at least 8 characters and avoid
                    reusing passwords from other accounts.</p>
                <p class="adviser-demo-note"><i class="fa-solid fa-circle-info"></i> This form is currently UI-only.
                    Password changes must be validated and saved securely by Laravel.</p>
                <div class="adviser-modal-actions">
                    <button type="button" class="adviser-modal-btn cancel" id="cancelChangePassword">Cancel</button>
                    <button type="submit" class="adviser-modal-btn save">
                        <i class="fa-solid fa-key"></i>
                        Update Password
                    </button>
                </div>
            </form>
        </section>
    </div>

    {{-- FEEDBACK MESSAGE --}}
    <div class="adviser-profile-toast" id="profileToast" role="status" aria-live="polite"></div>
@endsection

{{-- PAGE JS --}}
@section('page-js')
    <script src="{{ asset('js/adviser/adviser-profile.js') }}"></script>
@endsection