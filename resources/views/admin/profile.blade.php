@extends('layouts.admin')

@section('title', 'Admin Profile')

@section('header', 'My Profile')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/profile.css') }}">
@endsection

@section('content')

    <section class="profile-container">

        {{-- SUCCESS MESSAGE --}}
        @if (session('success'))
            <div class="profile-alert success-alert">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- GENERAL VALIDATION ERRORS --}}
        @if ($errors->any())
            <div class="profile-alert error-alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div>
                    <strong>Please check the following:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- PROFILE OVERVIEW --}}
        <div class="profile-overview-card">

            {{-- PROFILE PHOTO --}}
            <div class="profile-avatar-wrapper">

                <div class="profile-avatar" id="openPhotoUpload">

                    @if ($admin->profile_photo_path)
                        <img src="{{ asset('storage/' . $admin->profile_photo_path) }}" alt="Admin Profile Photo"
                            id="currentProfilePhoto">
                    @else
                        <i class="fa-solid fa-user"></i>
                    @endif

                </div>

                <button type="button" class="change-photo-btn" id="openPhotoUpload" title="Change Profile Picture">
                    <i class="fa-solid fa-camera"></i>
                </button>

            </div>

            <div class="profile-overview-info">
                <span class="profile-eyebrow">ADMINISTRATOR ACCOUNT</span>

                <h2>{{ $admin->name }}</h2>

                <p>{{ $admin->email }}</p>

                <div class="profile-badges">
                    <span class="role-badge">
                        <i class="fa-solid fa-shield-halved"></i>
                        {{ \Illuminate\Support\Str::headline($admin->role) }}
                    </span>

                    <span class="status-badge {{ $admin->account_status === 'active' ? 'active' : 'inactive' }}">
                        <span class="status-dot"></span>
                        {{ \Illuminate\Support\Str::headline($admin->account_status) }}
                    </span>
                </div>
            </div>

            <div class="profile-overview-action">
                <button type="button" class="primary-profile-btn" id="openEditProfile">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Edit Profile
                </button>
            </div>

        </div>

        {{-- PROFILE DETAILS --}}
        <div class="profile-details-grid">

            {{-- PERSONAL INFORMATION --}}
            <div class="profile-section-card">

                <div class="profile-section-header">
                    <div class="section-icon personal-icon">
                        <i class="fa-solid fa-user"></i>
                    </div>

                    <div>
                        <h3>Personal Information</h3>
                        <p>Your basic administrator information</p>
                    </div>
                </div>

                <div class="profile-detail-list">

                    <div class="profile-detail-item">
                        <span>Full Name</span>
                        <strong>{{ $admin->name }}</strong>
                    </div>

                    <div class="profile-detail-item">
                        <span>Email Address</span>
                        <strong>{{ $admin->email }}</strong>
                    </div>

                </div>

                <button type="button" class="text-action-btn" id="openEditProfileDetails">
                    <i class="fa-solid fa-pen"></i>
                    Edit Personal Information
                </button>

            </div>

            {{-- ACCOUNT INFORMATION --}}
            <div class="profile-section-card">

                <div class="profile-section-header">
                    <div class="section-icon account-icon">
                        <i class="fa-solid fa-id-card"></i>
                    </div>

                    <div>
                        <h3>Account Information</h3>
                        <p>Details about your system account</p>
                    </div>
                </div>

                <div class="profile-detail-list">

                    <div class="profile-detail-item">
                        <span>Account Role</span>
                        <strong>{{ \Illuminate\Support\Str::headline($admin->role) }}</strong>
                    </div>

                    <div class="profile-detail-item">
                        <span>Account Status</span>
                        <strong
                            class="{{ $admin->account_status === 'active' ? 'status-text-active' : 'status-text-inactive' }}">
                            {{ \Illuminate\Support\Str::headline($admin->account_status) }}
                        </strong>
                    </div>

                    <div class="profile-detail-item">
                        <span>Date Created</span>
                        <strong>{{ $admin->created_at?->format('F d, Y') ?? 'Not available' }}</strong>
                    </div>

                </div>

            </div>

        </div>

        {{-- ACCOUNT SECURITY --}}
        <div class="profile-section-card security-card">

            <div class="profile-section-header">
                <div class="section-icon security-icon">
                    <i class="fa-solid fa-lock"></i>
                </div>

                <div>
                    <h3>Account Security</h3>
                    <p>Keep your administrator account protected.</p>
                </div>
            </div>

            <div class="security-action-row">
                <div class="security-action-info">
                    <strong>Password</strong>
                    <span>Change your password to keep your account secure.</span>
                </div>

                <button type="button" class="secondary-profile-btn" id="openChangePassword">
                    <i class="fa-solid fa-key"></i>
                    Change Password
                </button>
            </div>

        </div>

    </section>

    {{-- =========================================
    PROFILE PHOTO MODAL
    ========================================= --}}
    <div class="profile-modal-overlay" id="profilePhotoModal">

        <div class="profile-modal">

            <div class="profile-modal-header">
                <div>
                    <h2>Profile Picture</h2>
                    <p>Upload or change your administrator photo.</p>
                </div>

                <button type="button" class="profile-modal-close" data-close-modal="profilePhotoModal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('admin.profile.photo.update') }}" method="POST" enctype="multipart/form-data">

                @csrf

                <div class="profile-modal-body">

                    <div class="photo-upload-preview">

                        <img src="{{ $admin->profile_photo_path ? asset('storage/' . $admin->profile_photo_path) : '' }}"
                            alt="Profile Preview" id="photoPreview"
                            style="{{ $admin->profile_photo_path ? '' : 'display:none;' }}">

                        <i class="fa-solid fa-user" id="photoPreviewIcon"
                            style="{{ $admin->profile_photo_path ? 'display:none;' : '' }}"></i>

                    </div>

                    <div class="profile-form-group">
                        <label for="profilePhotoInput">Choose Profile Picture</label>

                        <input type="file" id="profilePhotoInput" name="profile_photo" accept=".jpg,.jpeg,.png,.webp"
                            required>

                        <small class="field-hint">
                            JPG, JPEG, PNG, or WEBP. Maximum file size: 2 MB.
                        </small>

                        @error('profile_photo')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>

                </div>

                <div class="profile-modal-footer">

                    <button type="button" class="cancel-profile-btn" data-close-modal="profilePhotoModal">
                        Cancel
                    </button>

                    <button type="submit" class="primary-profile-btn">
                        <i class="fa-solid fa-upload"></i>
                        Save Photo
                    </button>

                </div>

            </form>

            {{-- REMOVE PROFILE PHOTO --}}
            @if ($admin->profile_photo_path)

                <form action="{{ route('admin.profile.photo.remove') }}" method="POST" class="remove-photo-form"
                    onsubmit="return confirm('Are you sure you want to remove your profile picture?');">

                    @csrf
                    @method('DELETE')

                    <button type="submit" class="remove-photo-btn">
                        <i class="fa-solid fa-trash"></i>
                        Remove Profile Picture
                    </button>

                </form>

            @endif

        </div>

    </div>



    {{-- =========================================
    EDIT PROFILE MODAL
    ========================================= --}}
    <div class="profile-modal-overlay" id="editProfileModal">

        <div class="profile-modal edit-profile-modal">

            <div class="profile-modal-header">
                <div>
                    <h2>Edit Profile</h2>
                    <p>Update your profile picture and personal information.</p>
                </div>

                <button type="button" class="profile-modal-close" data-close-modal="editProfileModal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- ONE FORM FOR PHOTO, NAME AND EMAIL --}}
            <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">

                @csrf
                @method('PUT')

                <div class="profile-modal-body">

                    {{-- PROFILE PHOTO --}}
                    <div class="edit-profile-photo-section">

                        <div class="edit-profile-photo-preview">

                            @if ($admin->profile_photo_path)
                                <img src="{{ asset('storage/' . $admin->profile_photo_path) }}" alt="Admin Profile Photo"
                                    id="editProfilePhotoPreview">
                            @else
                                <img src="" alt="Admin Profile Photo" id="editProfilePhotoPreview" style="display:none;">

                                <i class="fa-solid fa-user" id="editProfilePhotoIcon"></i>
                            @endif

                        </div>

                        <div class="edit-profile-photo-info">
                            <strong>Profile Picture</strong>
                            <span>Choose a photo for your administrator account.</span>
                            <small>JPG, JPEG, PNG, or WEBP · Maximum 2 MB</small>
                        </div>

                    </div>

                    <div class="profile-form-group">
                        <label for="editProfilePhotoInput">Change Profile Picture</label>

                        <input type="file" id="editProfilePhotoInput" name="profile_photo" accept=".jpg,.jpeg,.png,.webp">

                        @error('profile_photo')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="edit-profile-divider"></div>

                    {{-- FULL NAME --}}
                    <div class="profile-form-group">
                        <label for="profileName">Full Name</label>

                        <input type="text" id="profileName" name="name" value="{{ old('name', $admin->name) }}"
                            placeholder="Enter your full name" maxlength="255" required>

                        @error('name')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>

                    {{-- EMAIL ADDRESS --}}
                    <div class="profile-form-group">
                        <label for="profileEmail">Email Address</label>

                        <input type="email" id="profileEmail" name="email" value="{{ old('email', $admin->email) }}"
                            placeholder="Enter your email address" maxlength="255" required>

                        @error('email')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>

                </div>

                {{-- ONE SAVE BUTTON --}}
                <div class="profile-modal-footer">

                    <button type="button" class="cancel-profile-btn" data-close-modal="editProfileModal">
                        Cancel
                    </button>

                    <button type="submit" class="primary-profile-btn">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save Changes
                    </button>

                </div>

            </form>

            {{-- REMOVE PHOTO --}}
            @if ($admin->profile_photo_path)

                <form action="{{ route('admin.profile.photo.remove') }}" method="POST" class="remove-photo-form"
                    onsubmit="return confirm('Are you sure you want to remove your profile picture?');">

                    @csrf
                    @method('DELETE')

                    <button type="submit" class="remove-photo-btn">
                        <i class="fa-solid fa-trash"></i>
                        Remove Profile Picture
                    </button>

                </form>

            @endif

        </div>

    </div>

    {{-- REMOVE PHOTO FORM --}}
    @if ($admin->profile_photo_path)
        <form action="{{ route('admin.profile.photo.remove') }}" method="POST" id="removeEditProfilePhotoForm" hidden>
            @csrf
            @method('DELETE')
        </form>
    @endif


    {{-- =========================================
    CHANGE PASSWORD MODAL
    ========================================= --}}
    <div class="profile-modal-overlay" id="changePasswordModal">

        <div class="profile-modal">

            <div class="profile-modal-header">
                <div>
                    <h2>Change Password</h2>
                    <p>Enter your current password and choose a new one.</p>
                </div>

                <button type="button" class="profile-modal-close" data-close-modal="changePasswordModal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('admin.profile.password') }}" method="POST">

                @csrf
                @method('PUT')

                <div class="profile-modal-body">

                    <div class="profile-form-group">
                        <label for="currentPassword">Current Password</label>

                        <input type="password" id="currentPassword" name="current_password"
                            placeholder="Enter your current password" autocomplete="current-password" required>

                        @error('current_password')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="profile-form-group">
                        <label for="newPassword">New Password</label>

                        <input type="password" id="newPassword" name="password" placeholder="Enter your new password"
                            autocomplete="new-password" required>

                        @error('password')
                            <small class="field-error">{{ $message }}</small>
                        @enderror

                        <small class="field-hint">Use at least 8 characters.</small>
                    </div>

                    <div class="profile-form-group">
                        <label for="confirmPassword">Confirm New Password</label>

                        <input type="password" id="confirmPassword" name="password_confirmation"
                            placeholder="Confirm your new password" autocomplete="new-password" required>
                    </div>

                </div>

                <div class="profile-modal-footer">

                    <button type="button" class="cancel-profile-btn" data-close-modal="changePasswordModal">
                        Cancel
                    </button>

                    <button type="submit" class="primary-profile-btn">
                        <i class="fa-solid fa-lock"></i>
                        Update Password
                    </button>

                </div>

            </form>

        </div>

    </div>

@endsection



@section('page-js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // =========================================
            // MODALS
            // =========================================

            const editModal = document.getElementById('editProfileModal');
            const passwordModal = document.getElementById('changePasswordModal');
            const photoModal = document.getElementById('profilePhotoModal');

            const openEditButtons = [
                document.getElementById('openEditProfile'),
                document.getElementById('openEditProfileDetails')
            ];

            const openPasswordButton = document.getElementById('openChangePassword');
            const openPhotoButton = document.getElementById('openPhotoUpload');

            function openModal(modal) {
                if (!modal) return;

                modal.classList.add('show');
                document.body.classList.add('profile-modal-open');
            }

            function closeModal(modal) {
                if (!modal) return;

                modal.classList.remove('show');

                if (!document.querySelector('.profile-modal-overlay.show')) {
                    document.body.classList.remove('profile-modal-open');
                }
            }


            // =========================================
            // OPEN EDIT PROFILE MODAL
            // =========================================

            openEditButtons.forEach(function (button) {
                if (button) {
                    button.addEventListener('click', function () {
                        openModal(editModal);
                    });
                }
            });


            // =========================================
            // OPEN CHANGE PASSWORD MODAL
            // =========================================

            if (openPasswordButton) {
                openPasswordButton.addEventListener('click', function () {
                    openModal(passwordModal);
                });
            }


            // =========================================
            // OPEN PROFILE PHOTO MODAL
            // =========================================

            if (openPhotoButton) {
                openPhotoButton.addEventListener('click', function () {
                    openModal(photoModal);
                });
            }


            // =========================================
            // CLOSE MODALS
            // =========================================

            document.querySelectorAll('[data-close-modal]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const modal = document.getElementById(button.dataset.closeModal);
                    closeModal(modal);
                });
            });

            document.querySelectorAll('.profile-modal-overlay').forEach(function (overlay) {
                overlay.addEventListener('click', function (event) {
                    if (event.target === overlay) {
                        closeModal(overlay);
                    }
                });
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    document.querySelectorAll('.profile-modal-overlay.show').forEach(function (modal) {
                        closeModal(modal);
                    });
                }
            });


            // =========================================
            // EDIT PROFILE PHOTO LIVE PREVIEW
            // =========================================

            const editProfilePhotoInput = document.getElementById('editProfilePhotoInput');
            const editProfilePhotoPreview = document.getElementById('editProfilePhotoPreview');
            const editProfilePhotoIcon = document.getElementById('editProfilePhotoIcon');

            if (editProfilePhotoInput) {
                editProfilePhotoInput.addEventListener('change', function () {

                    const file = this.files[0];

                    if (!file) return;

                    const allowedTypes = [
                        'image/jpeg',
                        'image/png',
                        'image/webp'
                    ];

                    if (!allowedTypes.includes(file.type)) {
                        alert('Please select a JPG, PNG, or WEBP image.');
                        this.value = '';
                        return;
                    }

                    if (file.size > 2 * 1024 * 1024) {
                        alert('Image size must not exceed 2 MB.');
                        this.value = '';
                        return;
                    }

                    const reader = new FileReader();

                    reader.onload = function (event) {

                        if (editProfilePhotoPreview) {
                            editProfilePhotoPreview.src = event.target.result;
                            editProfilePhotoPreview.style.display = 'block';
                        }

                        if (editProfilePhotoIcon) {
                            editProfilePhotoIcon.style.display = 'none';
                        }

                    };

                    reader.readAsDataURL(file);

                });
            }


            // =========================================
            // PROFILE PHOTO MODAL LIVE PREVIEW
            // =========================================

            const photoInput = document.getElementById('profilePhotoInput');
            const photoPreview = document.getElementById('photoPreview');
            const photoPreviewIcon = document.getElementById('photoPreviewIcon');

            if (photoInput) {
                photoInput.addEventListener('change', function () {

                    const file = this.files[0];

                    if (!file) return;

                    const allowedTypes = [
                        'image/jpeg',
                        'image/png',
                        'image/webp'
                    ];

                    if (!allowedTypes.includes(file.type)) {
                        alert('Please select a JPG, PNG, or WEBP image.');
                        this.value = '';
                        return;
                    }

                    if (file.size > 2 * 1024 * 1024) {
                        alert('Image size must not exceed 2 MB.');
                        this.value = '';
                        return;
                    }

                    const reader = new FileReader();

                    reader.onload = function (event) {

                        if (photoPreview) {
                            photoPreview.src = event.target.result;
                            photoPreview.style.display = 'block';
                        }

                        if (photoPreviewIcon) {
                            photoPreviewIcon.style.display = 'none';
                        }

                    };

                    reader.readAsDataURL(file);

                });
            }


            // =========================================
            // REOPEN MODAL IF VALIDATION FAILS
            // =========================================

            @if ($errors->has('name') || $errors->has('email') || $errors->has('profile_photo'))
                openModal(editModal);
            @elseif ($errors->has('current_password') || $errors->has('password'))
                openModal(passwordModal);
            @endif

        });
    </script>
@endsection