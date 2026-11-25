@extends('layouts.admin')

@section('title', 'Settings')
@section('header', 'Settings')
@section('header-description', 'Manage school year, grading standards, notifications, and database backup.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/settings-admin.css') }}">
@endsection

@section('content')

    <section class="settings-container">

        {{-- SETTINGS SIDEBAR --}}
        <div class="settings-sidebar">

            <button type="button" class="settings-tab active" data-section="general">
                <i class="fa-solid fa-sliders"></i>
                <span>General Settings</span>
            </button>

            <button type="button" class="settings-tab" data-section="grades">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>Grade Settings</span>
            </button>

            <button type="button" class="settings-tab" data-section="notifications">
                <i class="fa-solid fa-bell"></i>
                <span>Notifications</span>
            </button>

            <button type="button" class="settings-tab" data-section="backup">
                <i class="fa-solid fa-database"></i>
                <span>Database Backup</span>
            </button>

        </div>

        <div class="settings-content">

            {{-- SESSION MESSAGES --}}
            @if(session('success'))
                <div class="settings-alert success">
                    <i class="fa-solid fa-circle-check"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="settings-alert error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="settings-alert error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div>
                        <strong>Please check the following:</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif


            {{-- GENERAL SETTINGS --}}
            <div class="settings-section active" id="general">

                <div class="settings-section-header">
                    <div>
                        <h2>General Settings</h2>
                        <p>Manage the school's information and academic year.</p>
                    </div>
                </div>

                {{-- SCHOOL INFORMATION --}}
                <div class="settings-subsection">
                    <h3>School Information</h3>
                    <p class="settings-description">
                        Official school information used throughout the grading system.
                    </p>

                    <div class="settings-form">

                        <div class="form-group">
                            <label>School Name</label>
                            <input type="text" value="Forbes Academy" readonly>
                            <small>This is the registered school name and cannot be changed here.</small>
                        </div>

                        <div class="form-row">

                            <div class="form-group">
                                <label>School Level</label>
                                <input type="text" value="Junior High School" readonly>
                            </div>

                            <div class="form-group">
                                <label>Grade Levels</label>
                                <input type="text" value="Grades 7–10" readonly>
                            </div>

                        </div>

                    </div>
                </div>


                {{-- SCHOOL YEAR MANAGEMENT --}}
                <div class="settings-subsection">

                    <div class="settings-subsection-heading">
                        <div>
                            <h3>School Year Management</h3>
                            <p class="settings-description">
                                Create and manage academic years for the grading system.
                            </p>
                        </div>

                        <button type="button" class="save-settings" id="openAddYearModal">
                            <i class="fa-solid fa-plus"></i>
                            Add School Year
                        </button>
                    </div>


                    {{-- ACTIVE SCHOOL YEAR --}}
                    <div class="active-school-year-card">

                        <div class="active-year-icon">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>

                        <div class="active-year-details">
                            <span class="active-year-label">Currently Active School Year</span>

                            @if($activeSchoolYear)
                                <h3>{{ $activeSchoolYear->year_name }}</h3>
                                <p>
                                    Current Term:
                                    <strong>{{ $activeSchoolYear->current_term_label }}</strong>
                                </p>
                            @else
                                <h3>No Active School Year</h3>
                                <p>Please activate a school year to continue.</p>
                            @endif
                        </div>

                        @if($activeSchoolYear)
                            <span class="year-status active">Active</span>
                        @else
                            <span class="year-status archived">No Active Year</span>
                        @endif
                    </div>


                    {{-- SCHOOL YEAR END DATE WARNING --}}
                    @if($activeSchoolYear && $activeSchoolYear->end_date)
                        @if(today()->lessThanOrEqualTo($activeSchoolYear->end_date))
                            <div class="school-year-date-warning">
                                <i class="fa-solid fa-calendar-clock"></i>
                                <div>
                                    <strong>School Year End Date Not Reached</strong>
                                    <p>
                                        The active school year is scheduled to end on
                                        {{ $activeSchoolYear->end_date->format('F d, Y') }}.
                                        School year activation for the next academic year is not
                                        yet allowed. You may continue managing grades and records.
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="school-year-date-notice">
                                <i class="fa-solid fa-circle-info"></i>
                                <div>
                                    <strong>School Year End Date Reached</strong>
                                    <p>
                                        The scheduled end date was
                                        {{ $activeSchoolYear->end_date->format('F d, Y') }}.
                                        The administrator may now consider activating the next
                                        school year, subject to pending grade submission checks
                                        and successful database backup.
                                    </p>
                                </div>
                            </div>
                        @endif
                    @endif



                    {{-- CURRENT TERM --}}
                    @if($activeSchoolYear)

                        <div class="term-management-card">

                            <div>
                                <h4>Current Term</h4>
                                <p>
                                    Select the term currently being used for grading
                                    under {{ $activeSchoolYear->year_name }}.
                                </p>
                            </div>

                            <form action="{{ route('admin.settings.school-years.term', $activeSchoolYear) }}" method="POST"
                                class="term-update-form">
                                @csrf
                                @method('PATCH')

                                <select name="current_term" required>
                                    <option value="1" {{ $activeSchoolYear->current_term == 1 ? 'selected' : '' }}>
                                        Term 1
                                    </option>
                                    <option value="2" {{ $activeSchoolYear->current_term == 2 ? 'selected' : '' }}>
                                        Term 2
                                    </option>
                                    <option value="3" {{ $activeSchoolYear->current_term == 3 ? 'selected' : '' }}>
                                        Term 3
                                    </option>
                                </select>

                                <button type="submit" class="save-settings">
                                    <i class="fa-solid fa-check"></i>
                                    Update Term
                                </button>

                            </form>

                        </div>

                    @endif


                    {{-- SCHOOL YEAR LIST --}}
                    <div class="school-year-list-container">

                        <h4>School Year Records</h4>

                        <div class="school-year-list">

                            @forelse($schoolYears as $schoolYear)

                                <div class="school-year-row">

                                    <div class="school-year-info">

                                        <strong>{{ $schoolYear->year_name }}</strong>

                                        <span>
                                            @if($schoolYear->start_date)
                                                {{ $schoolYear->start_date->format('M d, Y') }}
                                            @else
                                                Start date not set
                                            @endif

                                            <span class="date-separator">—</span>

                                            @if($schoolYear->end_date)
                                                {{ $schoolYear->end_date->format('M d, Y') }}
                                            @else
                                                End date not set
                                            @endif
                                        </span>

                                    </div>

                                    <div class="school-year-actions">

                                        <span class="year-status {{ $schoolYear->status }}">
                                            {{ ucfirst($schoolYear->status) }}
                                        </span>

                                        @if(in_array($schoolYear->status, ['active', 'upcoming']))
                                            <button type="button" class="edit-year-btn" data-id="{{ $schoolYear->id }}"
                                                data-year="{{ $schoolYear->year_name }}"
                                                data-start="{{ $schoolYear->start_date?->format('Y-m-d') }}"
                                                data-end="{{ $schoolYear->end_date?->format('Y-m-d') }}">
                                                <i class="fa-solid fa-pen"></i>
                                                Edit
                                            </button>

                                        @endif

                                        @if($schoolYear->status === 'upcoming')

                                            <form action="{{ route('admin.settings.school-years.activate', $schoolYear) }}"
                                                method="POST" class="activate-year-form">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit" class="activate-year-btn">
                                                    Activate
                                                </button>
                                            </form>

                                        @elseif($schoolYear->status === 'active')

                                            <span class="current-year-label">
                                                <i class="fa-solid fa-check-circle"></i>
                                                Current
                                            </span>

                                        @endif

                                    </div>

                                </div>

                            @empty

                                <div class="empty-school-years">
                                    <i class="fa-solid fa-calendar-xmark"></i>
                                    <p>No school year records found.</p>
                                </div>

                            @endforelse

                        </div>

                    </div>

                </div>


                {{-- SYSTEM STATUS --}}
                <div class="settings-subsection">

                    <h3>System Status</h3>

                    <div class="status-box">
                        <div class="status-indicator">
                            <span></span>
                            <div>
                                <strong>System Active</strong>
                                <p>The grading system is currently operational.</p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>


            {{-- GRADE SETTINGS --}}
            <div class="settings-section" id="grades">

                <div class="settings-section-header">
                    <div>
                        <h2>Grade Settings</h2>
                        <p>Official grading standards currently implemented in the system.</p>
                    </div>
                </div>

                <div class="settings-subsection">

                    <h3>Grading Standards</h3>

                    <div class="settings-form">

                        <div class="form-row">

                            <div class="form-group">
                                <label>Passing Grade</label>
                                <input type="number" value="75" readonly>
                            </div>

                            <div class="form-group">
                                <label>Maximum Grade</label>
                                <input type="number" value="100" readonly>
                            </div>

                        </div>

                    </div>

                </div>


                <div class="settings-subsection">

                    <h3>Grade Descriptors</h3>

                    <p class="settings-description">
                        The following descriptors are used to describe student academic performance.
                    </p>

                    <div class="grade-remarks">

                        <div class="remark-row">
                            <div>
                                <strong>90-100</strong>
                                <span>Namumukod-tangi</span>
                            </div>
                            <span class="remark-badge passed">Advancing</span>
                        </div>

                        <div class="remark-row">
                            <div>
                                <strong>80-89</strong>
                                <span>Napamamalas</span>
                            </div>
                            <span class="remark-badge passed">Benchmarking</span>
                        </div>

                        <div class="remark-row">
                            <div>
                                <strong>75-79</strong>
                                <span>Natutungo</span>
                            </div>
                            <span class="remark-badge passed">Connecting</span>
                        </div>

                        <div class="remark-row">
                            <div>
                                <strong>65-74</strong>
                                <span>Napauunlad</span>
                            </div>
                            <span class="remark-badge failed">Developing</span>
                        </div>

                        <div class="remark-row">
                            <div>
                                <strong>0-64</strong>
                                <span>Emerging</span>
                            </div>
                            <span class="remark-badge failed">Emerging</span>
                        </div>

                    </div>

                    <div class="settings-information">
                        <i class="fa-solid fa-circle-info"></i>
                        <p>
                            These grading standards are read-only to maintain consistency
                            across student records and grade computations.
                        </p>
                    </div>

                </div>


                <div class="settings-subsection">

                    <h3>Passing and Failing Remarks</h3>

                    <div class="remark-row">
                        <div>
                            <strong>75–100</strong>
                            <span>Passing range</span>
                        </div>
                        <span class="remark-badge passed">Passed</span>
                    </div>

                    <div class="remark-row">
                        <div>
                            <strong>Below 75</strong>
                            <span>Failing range</span>
                        </div>
                        <span class="remark-badge failed">Failed</span>
                    </div>

                </div>

            </div>


            {{-- NOTIFICATIONS --}}
            <div class="settings-section" id="notifications">

                <div class="settings-section-header">
                    <div>
                        <h2>Notifications</h2>
                        <p>Information about system notifications.</p>
                    </div>
                </div>

                <div class="settings-information">
                    <i class="fa-solid fa-circle-info"></i>
                    <p>
                        The system uses in-app notifications for important academic
                        activities. Notification preferences are not yet configurable
                        from this page.
                    </p>
                </div>

                <div class="notification-settings">

                    <div class="setting-item">
                        <div class="setting-info">
                            <i class="fa-solid fa-bullhorn"></i>
                            <div>
                                <strong>Announcement Notifications</strong>
                                <p>Announcements posted by the administrator.</p>
                            </div>
                        </div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-info">
                            <i class="fa-solid fa-file-circle-check"></i>
                            <div>
                                <strong>Grade Submission Notifications</strong>
                                <p>Updates regarding submitted grades and reviews.</p>
                            </div>
                        </div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-info">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <div>
                                <strong>System Notifications</strong>
                                <p>Important system-related announcements and updates.</p>
                            </div>
                        </div>
                    </div>

                </div>

            </div>




            {{-- DATABASE BACKUP --}}
            <div class="settings-section" id="backup">

                <div class="settings-section-header">
                    <div>
                        <h2>Database Backup</h2>
                        <p>Protect the system's academic and administrative records.</p>
                    </div>
                </div>

                <div class="data-management">

                    {{-- CREATE BACKUP --}}
                    <div class="data-card">

                        <div class="data-icon">
                            <i class="fa-solid fa-database"></i>
                        </div>

                        <div class="data-info">
                            <h3>Create Database Backup</h3>
                            <p>
                                Generate a downloadable backup of the system database,
                                including academic records and related data.
                            </p>
                            <span class="backup-note">
                                A new SQL backup file will be saved in the system's backup directory.
                            </span>
                        </div>

                        <form action="{{ route('admin.settings.backups.store') }}" method="POST" id="createBackupForm">
                            @csrf

                            <button type="submit" class="save-settings" id="createBackupBtn">
                                <i class="fa-solid fa-download"></i>
                                Create Backup
                            </button>
                        </form>

                    </div>

                </div>

                {{-- BACKUP HISTORY --}}
                <div class="backup-history-container" id="backupHistoryContainer"
                    data-index-url="{{ route('admin.settings.backups.index') }}"
                    data-download-url="{{ route('admin.settings.backups.download', ['filename' => '__BACKUP_FILENAME__']) }}"
                    data-restore-url="{{ route('admin.settings.backups.restore', ['filename' => '__BACKUP_FILENAME__']) }}">

                    <div class="backup-history-header">
                        <div>
                            <h3>Backup History</h3>
                            <p>View and manage previously created database backups.</p>
                        </div>

                        <button type="button" class="backup-refresh-btn" id="refreshBackupHistory">
                            <i class="fa-solid fa-rotate"></i>
                            Refresh
                        </button>
                    </div>

                    <div class="backup-table-wrapper">
                        <table class="backup-table">
                            <thead>
                                <tr>
                                    <th>Backup File</th>
                                    <th>File Size</th>
                                    <th>Date Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody id="backupHistoryBody">
                                <tr>
                                    <td colspan="4" class="backup-loading">
                                        <i class="fa-solid fa-spinner fa-spin"></i>
                                        Loading backup history...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>

                <div class="settings-information">
                    <i class="fa-solid fa-shield-halved"></i>
                    <p>
                        Restoring a backup replaces the current database contents with
                        the contents of the selected backup. A separate pre-restore
                        backup will automatically be created before restoration.
                        Always keep an additional copy of important backup files.
                    </p>
                </div>

            </div>


        </div>

    </section>


    {{-- ADD SCHOOL YEAR MODAL --}}
    <div class="settings-modal" id="addYearModal">

        <div class="settings-modal-box">

            <div class="settings-modal-header">
                <h2>Add School Year</h2>

                <button type="button" class="close-modal" data-close="addYearModal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <p>
                Create a new academic year. Newly created school years will be marked as Upcoming.
            </p>

            <form action="{{ route('admin.settings.school-years.store') }}" method="POST" id="addYearForm">
                @csrf

                <div class="form-group">
                    <label for="year_name">School Year Name <span>*</span></label>
                    <input type="text" id="year_name" name="year_name" placeholder="Example: 2027-2028"
                        value="{{ old('year_name') }}" pattern="[0-9]{4}-[0-9]{4}" required>
                    <small>Use the format YYYY-YYYY.</small>
                </div>

                <div class="form-row">

                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}">
                    </div>

                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}">
                    </div>

                </div>

                <div class="settings-information">
                    <i class="fa-solid fa-circle-info"></i>
                    <p>
                        Start Date and End Date are optional. You can add them when
                        the official school calendar is available.
                    </p>
                </div>

                <div class="modal-actions">

                    <button type="button" class="cancel-btn close-modal" data-close="addYearModal">
                        Cancel
                    </button>

                    <button type="submit" class="save-btn">
                        <i class="fa-solid fa-plus"></i>
                        Add School Year
                    </button>

                </div>

            </form>

        </div>

    </div>

    {{-- EDIT SCHOOL YEAR MODAL --}}
    <div class="settings-modal" id="editYearModal">

        <div class="settings-modal-box">

            <div class="settings-modal-header">
                <h2>Edit School Year</h2>

                <button type="button" class="close-modal" data-close="editYearModal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <p>
                Changes to the dates will apply to this school year only.
                Completed school years cannot be edited.
            </p>

            <form action="{{ route('admin.settings.school-years.update', '__YEAR_ID__') }}"
                data-url-template="{{ route('admin.settings.school-years.update', '__YEAR_ID__') }}" method="POST"
                id="editYearForm">
                @csrf
                @method('PATCH')

                <div class="form-group">
                    <label for="edit_year_name">
                        School Year Name <span>*</span>
                    </label>

                    <input type="text" id="edit_year_name" name="year_name" readonly>

                    <small>School year name cannot be changed after creation.</small>
                </div>

                <div class="form-row">

                    <div class="form-group">
                        <label for="edit_start_date">
                            Start Date <span>*</span>
                        </label>

                        <input type="date" id="edit_start_date" name="start_date" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_end_date">
                            End Date <span>*</span>
                        </label>

                        <input type="date" id="edit_end_date" name="end_date" required>
                    </div>

                </div>

                <div class="settings-information">
                    <i class="fa-solid fa-circle-info"></i>
                    <p>
                        Only upcoming school year dates can be edited.
                        Existing academic records will not be modified.
                    </p>
                </div>

                <div class="modal-actions">

                    <button type="button" class="cancel-btn close-modal" data-close="editYearModal">
                        Cancel
                    </button>

                    <button type="submit" class="save-btn">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save Changes
                    </button>

                </div>

            </form>

        </div>

    </div>


    {{-- RESTORE DATABASE CONFIRMATION MODAL --}}
    <div class="settings-modal" id="restoreBackupModal">

        <div class="settings-modal-box">

            <div class="settings-modal-header">
                <h2>Restore Database Backup</h2>

                <button type="button" class="close-modal" data-close="restoreBackupModal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="restore-warning">
                <i class="fa-solid fa-triangle-exclamation"></i>

                <div>
                    <strong>Warning: This action will replace current database data.</strong>
                    <p>
                        The selected backup will be restored to the current database.
                        Recent records that are not included in that backup may be lost.
                    </p>
                </div>
            </div>

            <div class="restore-selected-file">
                <span>Selected backup file</span>
                <strong id="restoreBackupFilename">No backup selected</strong>
            </div>

            <div class="settings-information">
                <i class="fa-solid fa-shield-halved"></i>
                <p>
                    The system will automatically create a separate backup
                    of the current database before proceeding.
                </p>
            </div>

            <form method="POST" id="restoreBackupForm"
                data-url-template="{{ route('admin.settings.backups.restore', ['filename' => '__BACKUP_FILENAME__']) }}">

                @csrf

                <div class="modal-actions">

                    <button type="button" class="cancel-btn close-modal" data-close="restoreBackupModal">
                        Cancel
                    </button>

                    <button type="submit" class="restore-confirm-btn" id="confirmRestoreBtn">
                        <i class="fa-solid fa-rotate-left"></i>
                        Restore Database
                    </button>

                </div>

            </form>

        </div>

    </div>



@endsection

@section('page-js')
    <script src="{{ asset('js/admin/settings-admin.js') }}"></script>
@endsection