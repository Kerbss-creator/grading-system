@extends('layouts.adviser')

@section('title', 'Settings')

@section('header', 'Settings')

@section('header-description', 'View your advisory information and manage your account security.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/adviser/adviser-settings.css') }}">
@endsection

@section('content')

    @php
        // Read-only information provided by the controller.
        $advisorySection = $advisorySection ?? 'Not assigned';
        $activeSchoolYear = $activeSchoolYear ?? 'Not available';
        $currentTerm = $currentTerm ?? 'Not available';
    @endphp

    <div class="adviser-settings-container">

        {{-- ADVISORY INFORMATION --}}
        <section class="adviser-settings-card">

            <div class="settings-card-header">
                <div class="settings-header-icon">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>

                <div class="settings-header-text">
                    <h3>Advisory Information</h3>
                    <p>View your assigned class and current academic period.</p>
                </div>
            </div>

            <div class="advisory-information-list">

                <div class="advisory-information-item">
                    <div class="advisory-information-icon">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>

                    <div class="advisory-information-content">
                        <span class="information-label">Assigned Section</span>
                        <span class="information-value">
                            {{ $advisorySection }}
                        </span>
                    </div>

                    <span class="information-status">
                        <i class="fa-solid fa-lock"></i>
                        Read-only
                    </span>
                </div>

                <div class="advisory-information-item">
                    <div class="advisory-information-icon">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>

                    <div class="advisory-information-content">
                        <span class="information-label">School Year</span>
                        <span class="information-value">
                            {{ $activeSchoolYear }}
                        </span>
                    </div>

                    <span class="information-status">
                        <i class="fa-solid fa-lock"></i>
                        Read-only
                    </span>
                </div>

                <div class="advisory-information-item">
                    <div class="advisory-information-icon">
                        <i class="fa-solid fa-book-open"></i>
                    </div>

                    <div class="advisory-information-content">
                        <span class="information-label">Current Term</span>
                        <span class="information-value">
                            {{ $currentTerm }}
                        </span>
                    </div>

                    <span class="information-status">
                        <i class="fa-solid fa-lock"></i>
                        Read-only
                    </span>
                </div>

            </div>

            <div class="settings-information-note">
                <i class="fa-solid fa-circle-info"></i>
                <p>
                    Advisory assignments and academic settings are managed by the administrator.
                    Contact the administrator if any information needs to be corrected.
                </p>
            </div>

        </section>

        {{-- PASSWORD SETTINGS --}}
        <section class="adviser-settings-card">

            <div class="settings-card-header">
                <div class="settings-header-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>

                <div class="settings-header-text">
                    <h3>Account Security</h3>
                    <p>Manage your account password.</p>
                </div>
            </div>

            <div class="password-settings-content">

                <div class="password-description">
                    <div class="password-icon">
                        <i class="fa-solid fa-lock"></i>
                    </div>

                    <div>
                        <h4>Change Password</h4>
                        <p>
                            Update your password to help keep your account secure.
                        </p>
                    </div>
                </div>

                <button type="button" class="change-password-btn" id="changePasswordBtn">
                    <i class="fa-solid fa-key"></i>
                    Change Password
                </button>

            </div>

            <div class="password-security-note">
                <i class="fa-solid fa-circle-check"></i>
                <span>
                    You can change your own password without administrator approval.
                </span>
            </div>

        </section>

    </div>

@endsection

@section('page-js')
    <script src="{{ asset('js/adviser/adviser-settings.js') }}"></script>
@endsection