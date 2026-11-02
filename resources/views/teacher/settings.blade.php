
@extends('layouts.teacher')

@section('title', 'Settings')

@section('header', 'Settings')

@section('header-description', 'Manage your account and view your system preferences.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/teacher/settings.css') }}">
@endsection

@section('content')

    @php
        $settingsUser = auth()->user();

        $teacherName = $settingsUser->name ?? 'lobotlobot';
        $teacherEmail = $settingsUser->email ?? 'saralobotan2026@gmail.com';

        // These values should be provided by the controller.
        $activeSchoolYear = $activeSchoolYear ?? 'Not available';
        $currentTerm = $currentTerm ?? 'Not available';
    @endphp

    <div class="settings-container">

        {{-- ACCOUNT SETTINGS --}}
        <div class="settings-card">

            <div class="settings-card-header">
                <div class="settings-header-icon">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div>
                    <h3>Account Settings</h3>
                    <p>View and manage your basic account information.</p>
                </div>
            </div>

            <form id="teacherSettingsForm" class="settings-form">

                @csrf

                <div class="form-group">
                    <label for="teacherName">Full Name</label>

                    <input
                        type="text"
                        id="teacherName"
                        name="name"
                        value="{{ old('name', $teacherName) }}"
                        placeholder="Enter your full name"
                        maxlength="255"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="teacherEmail">Email Address</label>

                    <input
                        type="email"
                        id="teacherEmail"
                        name="email"
                        value="{{ old('email', $teacherEmail) }}"
                        placeholder="Enter your email address"
                        maxlength="255"
                        required
                    >
                </div>

                <p class="settings-form-note">
                   NEED BACKEND DGDI PRE
                </p>

            </form>

        </div>

        {{-- SYSTEM PREFERENCES --}}
        <div class="settings-card">

            <div class="settings-card-header">
                <div class="settings-header-icon">
                    <i class="fa-solid fa-sliders"></i>
                </div>

                <div>
                    <h3>System Preferences</h3>
                    <p>View the current academic settings configured by the administrator.</p>
                </div>
            </div>

            <div class="settings-form">

                <div class="form-group">
                    <label for="schoolYear">School Year</label>

                    <input
                        type="text"
                        id="schoolYear"
                        value="{{ $activeSchoolYear }}"
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="term">Current Term</label>

                    <input
                        type="text"
                        id="term"
                        value="{{ $currentTerm }}"
                        readonly
                    >
                </div>

            </div>

            <p class="settings-form-note">
                SI ADMIN ANG BAHALA NA DGDI KAIPUHAN KI BACKEND WADAHEL
            </p>

        </div>

        {{-- PASSWORD --}}
        <div class="settings-card">

            <div class="settings-card-header">
                <div class="settings-header-icon">
                    <i class="fa-solid fa-lock"></i>
                </div>

                <div>
                    <h3>Password</h3>
                    <p>Keep your account secure by updating your password.</p>
                </div>
            </div>

            <div class="password-section">

                <div>
                    <h4>Change Password</h4>
                    <p>Update your account password when necessary.</p>
                </div>

                <button
                    type="button"
                    class="change-password-btn"
                    id="changePasswordBtn"
                >
                    <i class="fa-solid fa-key"></i>
                    Change Password
                </button>

            </div>

        </div>

        {{-- SAVE BUTTON --}}
        <div class="settings-actions">

            <button
                type="button"
                class="save-settings-btn"
                id="saveSettings"
            >
                <i class="fa-solid fa-check"></i>
                Save Changes
            </button>

        </div>

    </div>

@endsection

@section('page-js')
    <script src="{{ asset('js/teacher/settings.js') }}"></script>
@endsection
