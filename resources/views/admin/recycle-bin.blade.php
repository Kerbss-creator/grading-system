@extends('layouts.admin')

@section('title', 'Recycle Bin')
@section('header', 'Recycle Bin')
@section('header-description', 'View and restore deleted school records and accounts.')

@section('page-css')
<link rel="stylesheet" href="{{ asset('css/admin/recycle-bin.css') }}">
@endsection

@section('content')

<section class="recycle-bin">

    <!-- ALERT MESSAGES -->
    @if(session('success'))
    <div class="recycle-alert recycle-alert-success">
        <i class="fa-solid fa-circle-check"></i>
        {{ session('success') }}
    </div>
    @endif

    @if(session('warning'))
    <div class="recycle-alert recycle-alert-warning">
        <i class="fa-solid fa-triangle-exclamation"></i>
        {{ session('warning') }}
    </div>
    @endif

    @if($errors->any())
    <div class="recycle-alert recycle-alert-warning">
        <i class="fa-solid fa-circle-exclamation"></i>
        {{ $errors->first() }}
    </div>
    @endif

    <!-- INFORMATION -->
    <div class="recycle-info">
        <div class="recycle-info-icon">
            <i class="fa-solid fa-trash-can-arrow-up"></i>
        </div>

        <div>
            <h3>Deleted Records</h3>
            <p>
                Manage deleted records and restore them when necessary.
                Restoring a record will return it to its original management list.
            </p>
        </div>
    </div>

    <!-- CATEGORY TABS -->
    <div class="recycle-tabs"
        data-default-tab="{{ request('tab', 'students') }}">

        <button class="recycle-tab active" type="button" data-tab="students">
            <i class="fa-solid fa-graduation-cap"></i>
            Students
            <span>{{ $students->count() }}</span>
        </button>

        <button class="recycle-tab" type="button" data-tab="faculty">
            <i class="fa-solid fa-users"></i>
            Faculty
            <span>{{ $faculty->count() }}</span>
        </button>

        <button class="recycle-tab" type="button" data-tab="admins">
            <i class="fa-solid fa-user-shield"></i>
            Admins
            <span>{{ $admins->count() }}</span>
        </button>

        <button class="recycle-tab" type="button" data-tab="users">
            <i class="fa-solid fa-user-slash"></i>
            Unlinked Users
            <span>{{ $users->count() }}</span>
        </button>

    </div>

    <!-- TOOLBAR -->
    <div class="recycle-toolbar">

        <div class="recycle-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input
                type="text"
                id="recycleSearch"
                placeholder="Search deleted records...">
        </div>

        <div class="recycle-toolbar-label">
            <i class="fa-solid fa-circle-info"></i>
            Select a category to view deleted records.
        </div>

    </div>

    <!-- ==========================================
         STUDENTS
    ========================================== -->

    <div class="recycle-panel active" id="panel-students">

        <div class="recycle-panel-header">
            <div>
                <h3>Deleted Students</h3>
                <p>Previously deleted student profiles and login accounts.</p>
            </div>

            <span class="recycle-count">
                {{ $students->count() }} Records
            </span>
        </div>

        <div class="recycle-selection-toolbar">

            <form
                id="bulk-delete-students"
                method="POST"
                action="{{ route('admin.recycle-bin.force-delete-selected', ['type' => 'students']) }}"
                onsubmit="return confirm('Permanently delete the selected student records? This action cannot be undone.');">

                @csrf
                @method('DELETE')

                <input type="hidden" name="tab" value="{{ request('tab', 'students') }}">

                <span class="selected-count" data-selected-count>
                    0 selected
                </span>

                <button
                    type="button"
                    class="restore-selected-btn"
                    data-bulk-restore
                    disabled>
                    <i class="fa-solid fa-rotate-left"></i>
                    Restore Selected
                </button>

                <button
                    type="submit"
                    class="delete-selected-btn"
                    data-bulk-delete
                    disabled>
                    <i class="fa-solid fa-trash-can"></i>
                    Delete Selected Permanently
                </button>

            </form>

        </div>

        <div class="recycle-table-container">
            <table class="recycle-table">

                <thead>
                    <tr>
                        <th class="recycle-checkbox-column">
                            <input type="checkbox" class="recycle-checkbox select-all-checkbox"
                                data-select-all aria-label="Select all students">
                        </th>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Grade Level</th>
                        <th>Sex</th>
                        <th>School Year</th>
                        <th>Deleted At</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($students as $student)
                    <tr>
                        <td class="recycle-checkbox-column">
                            <input
                                type="checkbox"
                                class="recycle-checkbox record-checkbox"
                                data-record-checkbox
                                name="ids[]"
                                value="{{ $student->id }}"
                                form="bulk-delete-students"
                                aria-label="Select student {{ $student->student_id }}">
                        </td>

                        <td>{{ $student->student_id }}</td>

                        <td class="recycle-name">
                            {{ $student->first_name }}
                            {{ $student->middle_name }}
                            {{ $student->last_name }}
                        </td>

                        <td>{{ $student->grade_level }}</td>
                        <td>{{ $student->sex }}</td>
                        <td>{{ $student->school_year }}</td>
                        <td>{{ $student->deleted_at?->format('M d, Y h:i A') }}</td>

                        <td>
                            <div class="recycle-action-buttons">

                                <!-- INDIVIDUAL RESTORE -->
                                <form
                                    class="restore-record-form"
                                    method="POST"
                                    action="{{ route('admin.recycle-bin.restore', ['type' => 'students', 'id' => $student->id]) }}"
                                    onsubmit="return confirm('Restore this student record?');">

                                    @csrf
                                    <input type="hidden" name="tab" value="students">

                                    <button type="submit" class="restore-btn">
                                        <i class="fa-solid fa-rotate-left"></i>
                                        Restore
                                    </button>
                                </form>

                                <!-- PERMANENT DELETE -->
                                <form
                                    method="POST"
                                    action="{{ route('admin.recycle-bin.force-delete', ['type' => 'students', 'id' => $student->id]) }}"
                                    onsubmit="return confirm('Permanently delete this student record? This action cannot be undone.');">

                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="tab" value="students">

                                    <button type="submit" class="permanent-delete-btn">
                                        <i class="fa-solid fa-trash-can"></i>
                                        Delete Permanently
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="recycle-empty-row">
                        <td colspan="8">
                            <div class="recycle-empty">
                                <i class="fa-solid fa-graduation-cap"></i>
                                <h3>No Deleted Students</h3>
                                <p>Deleted student records will appear here.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>

    <!-- ==========================================
         FACULTY
    ========================================== -->

    <div class="recycle-panel" id="panel-faculty">

        <div class="recycle-panel-header">
            <div>
                <h3>Deleted Faculty</h3>
                <p>Previously deleted teacher and adviser profiles.</p>
            </div>

            <span class="recycle-count">
                {{ $faculty->count() }} Records
            </span>
        </div>

        <div class="recycle-selection-toolbar">

            <form
                id="bulk-delete-faculty"
                method="POST"
                action="{{ route('admin.recycle-bin.force-delete-selected', ['type' => 'faculty']) }}"
                onsubmit="return confirm('Permanently delete the selected faculty records? This action cannot be undone.');">

                @csrf
                @method('DELETE')

                <input type="hidden" name="tab" value="{{ request('tab', 'students') }}">

                <span class="selected-count" data-selected-count>
                    0 selected
                </span>

                <button type="button" class="restore-selected-btn"
                    data-bulk-restore disabled>
                    <i class="fa-solid fa-rotate-left"></i>
                    Restore Selected
                </button>

                <button type="submit" class="delete-selected-btn"
                    data-bulk-delete disabled>
                    <i class="fa-solid fa-trash-can"></i>
                    Delete Selected Permanently
                </button>

            </form>
        </div>

        <div class="recycle-table-container">
            <table class="recycle-table">

                <thead>
                    <tr>
                        <th class="recycle-checkbox-column">
                            <input type="checkbox" class="recycle-checkbox select-all-checkbox"
                                data-select-all aria-label="Select all faculty">
                        </th>
                        <th>Faculty ID</th>
                        <th>Faculty Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Deleted At</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($faculty as $member)
                    <tr>
                        <td class="recycle-checkbox-column">
                            <input
                                type="checkbox"
                                class="recycle-checkbox record-checkbox"
                                data-record-checkbox
                                name="ids[]"
                                value="{{ $member->id }}"
                                form="bulk-delete-faculty"
                                aria-label="Select faculty {{ $member->faculty_id }}">
                        </td>

                        <td>{{ $member->faculty_id }}</td>

                        <td class="recycle-name">
                            {{ $member->first_name }}
                            {{ $member->middle_name }}
                            {{ $member->last_name }}
                        </td>

                        <td>{{ $member->email }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $member->role)) }}</td>
                        <td>{{ ucfirst($member->status) }}</td>
                        <td>{{ $member->deleted_at?->format('M d, Y h:i A') }}</td>

                        <td>
                            <div class="recycle-action-buttons">

                                <form
                                    class="restore-record-form"
                                    method="POST"
                                    action="{{ route('admin.recycle-bin.restore', ['type' => 'faculty', 'id' => $member->id]) }}"
                                    onsubmit="return confirm('Restore this faculty record?');">

                                    @csrf
                                    <input type="hidden" name="tab" value="faculty">

                                    <button type="submit" class="restore-btn">
                                        <i class="fa-solid fa-rotate-left"></i>
                                        Restore
                                    </button>
                                </form>

                                <form
                                    method="POST"
                                    action="{{ route('admin.recycle-bin.force-delete', ['type' => 'faculty', 'id' => $member->id]) }}"
                                    onsubmit="return confirm('Permanently delete this faculty record? This action cannot be undone.');">

                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="tab" value="faculty">

                                    <button type="submit" class="permanent-delete-btn">
                                        <i class="fa-solid fa-trash-can"></i>
                                        Delete Permanently
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="recycle-empty-row">
                        <td colspan="8">
                            <div class="recycle-empty">
                                <i class="fa-solid fa-users"></i>
                                <h3>No Deleted Faculty</h3>
                                <p>Deleted faculty records will appear here.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>

    <!-- ==========================================
         ADMINS
    ========================================== -->

    <div class="recycle-panel" id="panel-admins">

        <div class="recycle-panel-header">
            <div>
                <h3>Deleted Admin Accounts</h3>
                <p>Previously deleted administrator login accounts.</p>
            </div>

            <span class="recycle-count">
                {{ $admins->count() }} Records
            </span>
        </div>

        <div class="recycle-selection-toolbar">

            <form
                id="bulk-delete-admins"
                method="POST"
                action="{{ route('admin.recycle-bin.force-delete-selected', ['type' => 'admins']) }}"
                onsubmit="return confirm('Permanently delete the selected administrator accounts? This action cannot be undone.');">

                @csrf
                @method('DELETE')

                <input type="hidden" name="tab" value="{{ request('tab', 'students') }}">

                <span class="selected-count" data-selected-count>
                    0 selected
                </span>

                <button type="button" class="restore-selected-btn"
                    data-bulk-restore disabled>
                    <i class="fa-solid fa-rotate-left"></i>
                    Restore Selected
                </button>

                <button type="submit" class="delete-selected-btn"
                    data-bulk-delete disabled>
                    <i class="fa-solid fa-trash-can"></i>
                    Delete Selected Permanently
                </button>

            </form>
        </div>

        <div class="recycle-table-container">
            <table class="recycle-table">

                <thead>
                    <tr>
                        <th class="recycle-checkbox-column">
                            <input type="checkbox" class="recycle-checkbox select-all-checkbox"
                                data-select-all aria-label="Select all administrators">
                        </th>
                        <th>Account ID</th>
                        <th>Administrator Name</th>
                        <th>Email</th>
                        <th>Deleted At</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($admins as $admin)
                    <tr>
                        <td class="recycle-checkbox-column">
                            <input
                                type="checkbox"
                                class="recycle-checkbox record-checkbox"
                                data-record-checkbox
                                name="ids[]"
                                value="{{ $admin->id }}"
                                form="bulk-delete-admins"
                                aria-label="Select administrator {{ $admin->name }}">
                        </td>

                        <td>{{ $admin->id }}</td>
                        <td class="recycle-name">{{ $admin->name }}</td>
                        <td>{{ $admin->email }}</td>
                        <td>{{ $admin->deleted_at?->format('M d, Y h:i A') }}</td>

                        <td>
                            <div class="recycle-action-buttons">

                                <form
                                    class="restore-record-form"
                                    method="POST"
                                    action="{{ route('admin.recycle-bin.restore', ['type' => 'admins', 'id' => $admin->id]) }}"
                                    onsubmit="return confirm('Restore this administrator account?');">

                                    @csrf
                                    <input type="hidden" name="tab" value="admins">

                                    <button type="submit" class="restore-btn">
                                        <i class="fa-solid fa-rotate-left"></i>
                                        Restore
                                    </button>
                                </form>

                                <form
                                    method="POST"
                                    action="{{ route('admin.recycle-bin.force-delete', ['type' => 'admins', 'id' => $admin->id]) }}"
                                    onsubmit="return confirm('Permanently delete this administrator account? This action cannot be undone.');">

                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="tab" value="admins">

                                    <button type="submit" class="permanent-delete-btn">
                                        <i class="fa-solid fa-trash-can"></i>
                                        Delete Permanently
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="recycle-empty-row">
                        <td colspan="6">
                            <div class="recycle-empty">
                                <i class="fa-solid fa-user-shield"></i>
                                <h3>No Deleted Admins</h3>
                                <p>Deleted administrator accounts will appear here.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>

    <!-- ==========================================
         UNLINKED USERS
    ========================================== -->

    <div class="recycle-panel" id="panel-users">

        <div class="recycle-panel-header">
            <div>
                <h3>Unlinked Deleted Users</h3>
                <p>Deleted login accounts not connected to student or faculty records.</p>
            </div>

            <span class="recycle-count">
                {{ $users->count() }} Records
            </span>
        </div>

        <div class="recycle-selection-toolbar">

            <form
                id="bulk-delete-users"
                method="POST"
                action="{{ route('admin.recycle-bin.force-delete-selected', ['type' => 'users']) }}"
                onsubmit="return confirm('Permanently delete the selected unlinked user accounts? This action cannot be undone.');">

                @csrf
                @method('DELETE')

                <input type="hidden" name="tab" value="{{ request('tab', 'students') }}">

                <span class="selected-count" data-selected-count>
                    0 selected
                </span>

                <button type="button" class="restore-selected-btn"
                    data-bulk-restore disabled>
                    <i class="fa-solid fa-rotate-left"></i>
                    Restore Selected
                </button>

                <button type="submit" class="delete-selected-btn"
                    data-bulk-delete disabled>
                    <i class="fa-solid fa-trash-can"></i>
                    Delete Selected Permanently
                </button>

            </form>
        </div>

        <div class="recycle-table-container">
            <table class="recycle-table">

                <thead>
                    <tr>
                        <th class="recycle-checkbox-column">
                            <input type="checkbox" class="recycle-checkbox select-all-checkbox"
                                data-select-all aria-label="Select all unlinked users">
                        </th>
                        <th>Account ID</th>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Deleted At</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="recycle-checkbox-column">
                            <input
                                type="checkbox"
                                class="recycle-checkbox record-checkbox"
                                data-record-checkbox
                                name="ids[]"
                                value="{{ $user->id }}"
                                form="bulk-delete-users"
                                aria-label="Select user {{ $user->name }}">
                        </td>

                        <td>{{ $user->id }}</td>
                        <td class="recycle-name">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $user->role)) }}</td>
                        <td>{{ $user->deleted_at?->format('M d, Y h:i A') }}</td>

                        <td>
                            <div class="recycle-action-buttons">

                                <form
                                    class="restore-record-form"
                                    method="POST"
                                    action="{{ route('admin.recycle-bin.restore', ['type' => 'users', 'id' => $user->id]) }}"
                                    onsubmit="return confirm('Restore this user account?');">

                                    @csrf
                                    <input type="hidden" name="tab" value="users">

                                    <button type="submit" class="restore-btn">
                                        <i class="fa-solid fa-rotate-left"></i>
                                        Restore
                                    </button>
                                </form>

                                <form
                                    method="POST"
                                    action="{{ route('admin.recycle-bin.force-delete', ['type' => 'users', 'id' => $user->id]) }}"
                                    onsubmit="return confirm('Permanently delete this user account? This action cannot be undone.');">

                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="tab" value="users">

                                    <button type="submit" class="permanent-delete-btn">
                                        <i class="fa-solid fa-trash-can"></i>
                                        Delete Permanently
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="recycle-empty-row">
                        <td colspan="7">
                            <div class="recycle-empty">
                                <i class="fa-solid fa-user-slash"></i>
                                <h3>No Unlinked Users</h3>
                                <p>Unlinked deleted accounts will appear here.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>

</section>

@endsection

@section('page-js')
<script src="{{ asset('js/admin/recycle-bin.js') }}"></script>
@endsection