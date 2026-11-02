@extends('layouts.student')

@section('title', 'My Profile')
@section('header', 'My Profile')
@section('header-description', 'View and manage your personal and account information.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/student/student-profile.css') }}">
@endsection

@section('content')
    @php
        $profileUser = auth()->user();

        $profileName = data_get($profileUser, 'name', 'Name not available');
        $profileEmail = data_get($profileUser, 'email', '');
        $studentId = data_get($profileUser, 'student_id', null);
        $contactNumber = data_get($profileUser, 'contact_number', null);
        $gradeLevel = data_get($profileUser, 'grade_level', null);
        $section = data_get($profileUser, 'section', null);
        $birthday = data_get($profileUser, 'date_of_birth', null);
        $gender = data_get($profileUser, 'gender', null);
        $address = data_get($profileUser, 'address', null);
        $username = data_get($profileUser, 'username', null);
        $accountStatus = data_get($profileUser, 'status', 'Active');

        $initials = collect(explode(' ', trim($profileName)))
            ->filter()
            ->take(2)
            ->map(fn($part) => strtoupper(substr($part, 0, 1)))
            ->implode('');

        $display = fn($value) => filled($value) ? $value : 'Not provided';
    @endphp

    <div class="student-profile-container">

        {{-- PROFILE HEADER --}}
        <section class="student-profile-hero">
            <div class="student-profile-identity">
                <div class="student-profile-avatar">
                    {{ $initials ?: 'S' }}
                </div>

                <div class="student-profile-heading">
                    <h2 id="profileDisplayName">{{ $profileName }}</h2>
                    <p>Junior High School Student</p>

                    <div class="student-profile-meta">
                        <span>
                            <i class="fa-solid fa-graduation-cap"></i>
                            {{ $gradeLevel ? 'Grade ' . $gradeLevel : 'Grade level not assigned' }}
                            @if($section)
                                - {{ $section }}
                            @endif
                        </span>

                        <span>
                            <i class="fa-regular fa-id-card"></i>
                            Student ID: {{ $display($studentId) }}
                        </span>
                    </div>

                    <span class="student-status">
                        <i class="fa-solid fa-circle"></i>
                        {{ ucfirst($accountStatus) }}
                    </span>
                </div>
            </div>

            <div class="student-profile-actions">
                <button type="button" class="student-profile-btn outline" id="editProfileBtn">
                    <i class="fa-solid fa-pen"></i>
                    Edit Profile
                </button>

                <button type="button" class="student-profile-btn primary" id="changePasswordBtn">
                    <i class="fa-solid fa-lock"></i>
                    Change Password
                </button>
            </div>
        </section>

        {{-- PROFILE CARDS --}}
        <div class="student-profile-grid">

            {{-- PERSONAL INFORMATION --}}
            <section class="student-profile-card">
                <div class="student-card-heading">
                    <div class="student-card-icon">
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <div>
                        <h3>Personal Information</h3>
                        <p>Your basic personal information.</p>
                    </div>
                </div>

                <div class="student-info-list">
                    <div class="student-info-item">
                        <i class="fa-solid fa-user"></i>
                        <div>
                            <span>Full Name</span>
                            <strong id="displayFullName">{{ $profileName }}</strong>
                        </div>
                    </div>

                    <div class="student-info-item">
                        <i class="fa-regular fa-calendar"></i>
                        <div>
                            <span>Date of Birth</span>
                            <strong>{{ $display($birthday) }}</strong>
                        </div>
                    </div>

                    <div class="student-info-item">
                        <i class="fa-solid fa-venus-mars"></i>
                        <div>
                            <span>Gender</span>
                            <strong>{{ $display($gender) }}</strong>
                        </div>
                    </div>

                    <div class="student-info-item">
                        <i class="fa-regular fa-envelope"></i>
                        <div>
                            <span>Email Address</span>
                            <strong id="displayEmail">{{ $display($profileEmail) }}</strong>
                        </div>
                    </div>

                    <div class="student-info-item">
                        <i class="fa-solid fa-phone"></i>
                        <div>
                            <span>Contact Number</span>
                            <strong id="displayContact">{{ $display($contactNumber) }}</strong>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ACADEMIC INFORMATION --}}
            <section class="student-profile-card">
                <div class="student-card-heading">
                    <div class="student-card-icon">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div>
                        <h3>Academic Information</h3>
                        <p>Your school-related information.</p>
                    </div>
                </div>

                <div class="student-info-list">
                    <div class="student-info-item">
                        <i class="fa-regular fa-id-card"></i>
                        <div>
                            <span>Student ID</span>
                            <strong>{{ $display($studentId) }}</strong>
                        </div>
                    </div>

                    <div class="student-info-item">
                        <i class="fa-solid fa-school"></i>
                        <div>
                            <span>Grade Level</span>
                            <strong>{{ $gradeLevel ? 'Grade ' . $gradeLevel : 'Not assigned' }}</strong>
                        </div>
                    </div>

                    <div class="student-info-item">
                        <i class="fa-solid fa-people-group"></i>
                        <div>
                            <span>Section</span>
                            <strong>{{ $display($section) }}</strong>
                        </div>
                    </div>

                    <div class="student-info-item">
                        <i class="fa-regular fa-calendar-check"></i>
                        <div>
                            <span>Enrollment Status</span>
                            <strong>
                                <span class="student-status-pill">
                                    {{ ucfirst($accountStatus) }}
                                </span>
                            </strong>
                        </div>
                    </div>
                </div>
            </section>

            {{-- RESIDENTIAL ADDRESS --}}
            <section class="student-profile-card">
                <div class="student-card-heading">
                    <div class="student-card-icon">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <div>
                        <h3>Residential Address</h3>
                        <p>Your current residential address.</p>
                    </div>
                </div>

                <div class="student-address-content">
                    <i class="fa-solid fa-house"></i>

                    <div class="student-address-text">
                        <p id="displayAddress">{{ $display($address) }}</p>
                        <small>
                            If your address needs correction, submit an update
                            request to the school administration.
                        </small>
                    </div>
                </div>

                <button type="button" class="student-profile-btn outline address-request-btn" id="requestAddressBtn">
                    <i class="fa-regular fa-paper-plane"></i>
                    Request Update
                </button>
            </section>

            {{-- ACCOUNT INFORMATION --}}
            <section class="student-profile-card">
                <div class="student-card-heading">
                    <div class="student-card-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h3>Account Information</h3>
                        <p>Your system account details.</p>
                    </div>
                </div>

                <div class="student-info-list">
                    <div class="student-info-item">
                        <i class="fa-regular fa-user"></i>
                        <div>
                            <span>Username</span>
                            <strong>{{ $display($username) }}</strong>
                        </div>
                    </div>

                    <div class="student-info-item">
                        <i class="fa-regular fa-envelope"></i>
                        <div>
                            <span>Email Verification</span>
                            <strong>
                                {{ data_get($profileUser, 'email_verified_at')
        ? 'Verified' : 'Not verified' }}
                            </strong>
                        </div>
                    </div>

                    <div class="student-info-item">
                        <i class="fa-solid fa-shield"></i>
                        <div>
                            <span>Account Status</span>
                            <strong>
                                <span class="student-status-pill">
                                    {{ ucfirst($accountStatus) }}
                                </span>
                            </strong>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- EDIT PROFILE MODAL --}}
    <div class="student-modal-overlay" id="editProfileModal" aria-hidden="true">
        <section class="student-modal" role="dialog" aria-modal="true" aria-labelledby="editProfileTitle">

            <div class="student-modal-header">
                <div>
                    <h3 id="editProfileTitle">Edit Profile</h3>
                    <p>Update your contact information.</p>
                </div>
                <button type="button" class="student-modal-close" id="closeEditProfile" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="editProfileForm">
                <div class="student-form-grid">
                    <div class="student-form-group">
                        <label for="editFullName">Full Name</label>
                        <input type="text" id="editFullName" name="name" value="{{ $profileName }}" maxlength="255"
                            required>
                    </div>

                    <div class="student-form-group">
                        <label for="editStudentId">Student ID</label>
                        <input type="text" id="editStudentId" value="{{ $studentId ?? 'Not assigned' }}" disabled>
                    </div>

                    <div class="student-form-group">
                        <label for="editEmail">Email Address</label>
                        <input type="email" id="editEmail" name="email" value="{{ $profileEmail }}" maxlength="255"
                            required>
                    </div>

                    <div class="student-form-group">
                        <label for="editContact">Contact Number</label>
                        <input type="tel" id="editContact" name="contact_number" value="{{ $contactNumber ?? '' }}"
                            maxlength="30" autocomplete="tel" placeholder="Enter contact number">
                    </div>
                </div>

                <p class="student-form-note">
                    <i class="fa-solid fa-circle-info"></i>
                    Changes shown here are a preview only until connected to
                    the Laravel backend.
                </p>

                <div class="student-modal-actions">
                    <button type="button" class="student-modal-btn cancel" id="cancelEditProfile">Cancel</button>
                    <button type="submit" class="student-modal-btn save">
                        <i class="fa-solid fa-check"></i> Save Changes
                    </button>
                </div>
            </form>
        </section>
    </div>

    {{-- CHANGE PASSWORD MODAL --}}
    <div class="student-modal-overlay" id="changePasswordModal" aria-hidden="true">
        <section class="student-modal password-modal" role="dialog" aria-modal="true" aria-labelledby="changePasswordTitle">

            <div class="student-modal-header">
                <div>
                    <h3 id="changePasswordTitle">Change Password</h3>
                    <p>Keep your account secure.</p>
                </div>
                <button type="button" class="student-modal-close" id="closeChangePassword" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="changePasswordForm">
                <div class="student-form-group">
                    <label for="currentPassword">Current Password</label>
                    <div class="student-password-input">
                        <input type="password" id="currentPassword" autocomplete="current-password" required>
                        <button type="button" class="toggle-student-password" data-target="currentPassword"
                            aria-label="Show password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="student-form-group">
                    <label for="newPassword">New Password</label>
                    <div class="student-password-input">
                        <input type="password" id="newPassword" minlength="8" autocomplete="new-password" required>
                        <button type="button" class="toggle-student-password" data-target="newPassword"
                            aria-label="Show password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="student-form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <div class="student-password-input">
                        <input type="password" id="confirmPassword" minlength="8" autocomplete="new-password" required>
                        <button type="button" class="toggle-student-password" data-target="confirmPassword"
                            aria-label="Show password">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <p class="student-form-note">
                    <i class="fa-solid fa-circle-info"></i>
                    Use at least 8 characters. Password changes require
                    secure backend verification.
                </p>

                <div class="student-modal-actions">
                    <button type="button" class="student-modal-btn cancel" id="cancelChangePassword">Cancel</button>
                    <button type="submit" class="student-modal-btn save">
                        <i class="fa-solid fa-lock"></i> Update Password
                    </button>
                </div>
            </form>
        </section>
    </div>

    {{-- TOAST --}}
    <div class="student-profile-toast" id="studentProfileToast" role="status" aria-live="polite"></div>
@endsection

@section('page-js')
    <script src="{{ asset('js/student/student-profile.js') }}"></script>
@endsection