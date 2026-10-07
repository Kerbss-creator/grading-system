`@extends('layouts.teacher')

@section('title', 'Settings')

@section('header', 'Settings')

@section('header-description', 'Manage your account and system preferences.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/teacher/settings.css') }}">
@endsection

@section('content')

    <div class="settings-container">

        {{-- ACCOUNT SETTINGS --}}
        <div class="settings-card">

            <div class="settings-card-header">
                <div class="settings-header-icon">
                    <i class="fa-solid fa-user"></i>
                </div>

                <div>
                    <h3>Account Settings</h3>
                    <p>Manage your basic account information.</p>
                </div>
            </div>

            <div class="settings-form">

                <div class="form-group">
                    <label for="teacherName">Full Name</label>

                    <input
                        type="text"
                        id="teacherName"
                        value="Teacher"
                        placeholder="Enter your full name"
                    >
                </div>

                <div class="form-group">
                    <label for="teacherEmail">Email Address</label>

                    <input
                        type="email"
                        id="teacherEmail"
                        value="teacher@forbesacademy.edu.ph"
                        placeholder="Enter your email address"
                    >
                </div>

            </div>

        </div>


        {{-- NOTIFICATION SETTINGS --}}
        <div class="settings-card">

            <div class="settings-card-header">
                <div class="settings-header-icon">
                    <i class="fa-solid fa-bell"></i>
                </div>

                <div>
                    <h3>Notification Settings</h3>
                    <p>Choose which notifications you want to receive.</p>
                </div>
            </div>

            <div class="settings-options">

                <div class="setting-option">

                    <div class="setting-option-info">
                        <h4>Announcements</h4>
                        <p>Receive notifications when new announcements are posted.</p>
                    </div>

                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>

                </div>

                <div class="setting-option">

                    <div class="setting-option-info">
                        <h4>Grade Submission</h4>
                        <p>Receive reminders and updates about grade submissions.</p>
                    </div>

                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>

                </div>

            </div>

        </div>


        {{-- SYSTEM PREFERENCES --}}
        <div class="settings-card">

            <div class="settings-card-header">
                <div class="settings-header-icon">
                    <i class="fa-solid fa-sliders"></i>
                </div>

                <div>
                    <h3>System Preferences</h3>
                    <p>View your current academic settings.</p>
                </div>
            </div>

            <div class="settings-form">

                <div class="form-group">

                    <label for="schoolYear">School Year</label>

                    <select id="schoolYear">
                        <option selected>2026-2027</option>
                        <option>2027-2028</option>
                    </select>

                </div>

                <div class="form-group">

                    <label for="term">Current Term</label>

                    <select id="term">
                        <option selected>1st Term</option>
                        <option>2nd Term</option>
                        <option>3rd Term</option>
                    </select>

                </div>

            </div>

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
                    <p>Update your account password if necessary.</p>
                </div>

                <button type="button" class="change-password-btn">
                    <i class="fa-solid fa-key"></i>
                    Change Password
                </button>

            </div>

        </div>


        {{-- SAVE BUTTON --}}
        <div class="settings-actions">

            <button type="button" class="save-settings-btn" id="saveSettings">
                <i class="fa-solid fa-check"></i>
                Save Changes
            </button>

        </div>

    </div>

@endsection


@section('page-js')
    <script src="{{ asset('js/teacher/settings.js') }}"></script>
@endsection
