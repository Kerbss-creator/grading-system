@extends('layouts.admin')
@section('title','User Management')
@section('header','User Management')
@section('header-description','Manage database accounts, CSV records and login access.')
@section('page-css')
<link rel="stylesheet" href="{{ asset('css/admin/records-data.css') }}">
@endsection
@section('content')
<div class="records-panel">
    <div class="records-row records-heading-actions"><h2>School Account Records</h2><a class="records-button" href="{{ route('admin.admins.index') }}">Administrator Management</a></div>
    <p class="subtle">The MySQL database is authoritative. Download CSV to edit names, IDs or emails; use Import CSV to review changes before saving. Passwords are never included in CSV files.</p>
    <div class="records-row">
        <a class="records-button" href="{{ route('admin.imports.index','students') }}"><i class="fa-solid fa-file-import"></i> Import Students</a>
        <a class="records-button" href="{{ route('admin.exports.csv','students') }}"><i class="fa-solid fa-download"></i> Export Students</a>
        <a class="records-button" href="{{ route('admin.imports.index','teachers') }}"><i class="fa-solid fa-file-import"></i> Import Teachers</a>
        <a class="records-button" href="{{ route('admin.exports.csv','teachers') }}"><i class="fa-solid fa-download"></i> Export Teachers</a>
        <a class="records-button" href="{{ route('admin.imports.index','admins') }}"><i class="fa-solid fa-file-import"></i> Import Admins</a>
        <a class="records-button" href="{{ route('admin.exports.csv','admins') }}"><i class="fa-solid fa-download"></i> Export Admins</a>
    </div>
    <div class="records-grid">
        <div><strong>{{ (int)($counts['student'] ?? 0) }}</strong><p class="records-small">Students</p></div>
        <div><strong>{{ (int)($counts['teacher'] ?? 0) }}</strong><p class="records-small">Teachers</p></div>
        <div><strong>{{ (int)($counts['admin'] ?? 0) }}</strong><p class="records-small">Administrators</p></div>
        <div><strong>{{ array_sum($counts->all()) }}</strong><p class="records-small">Total accounts</p></div>
    </div>
</div>
@if(session('success'))<div class="records-panel records-message">{{ session('success') }}</div>@endif
@if($errors->any())<div class="records-panel records-message records-error">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<div class="records-panel">
    <h2>Registered Accounts</h2>
    <form method="GET" action="{{ route('admin.users') }}" class="records-row records-filter-form" id="accountFilterForm">
        <label>Role <select name="role" aria-label="Filter accounts by role" onchange="this.form.requestSubmit()"><option value="">All</option>@foreach(['student','teacher','admin'] as $role)<option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>@endforeach</select></label>
        <label>Search <input class="records-input" name="search" value="{{ request('search') }}" placeholder="Name, email or username"></label>
        <button class="submit" type="submit">Search</button>
        <a class="records-button" href="{{ route('admin.users') }}">Clear</a>
    </form>
    <form id="removeAccountsForm" method="POST" action="{{ route('admin.users.bulkDestroy') }}" data-confirm="Move selected accounts and their linked school records to Recycle Bin? Their logins will be blocked.">@csrf @method('DELETE')</form>
        <div class="records-row records-heading-actions"><button class="records-button records-danger" type="submit" form="removeAccountsForm">Delete Selected Accounts</button><span class="records-small">Your own account and protected accounts cannot be selected.</span></div>
        <div class="records-scroll">
            <table class="records-data-table">
                <thead><tr><th>Select</th><th>Account ID</th><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($accounts as $account)
                    <tr>
                        <td>@if($account->id !== auth()->id() && !\App\Support\SystemOwner::is($account))<input type="checkbox" name="ids[]" form="removeAccountsForm" value="{{ $account->id }}" aria-label="Select account {{ $account->name }}">@endif</td>
                        <td>{{ \App\Support\SystemOwner::is($account) ? '404' : $account->id }}</td>
                        <td>{{ $account->name }}</td>
                        <td><code>{{ $account->username ?? "—" }}</code></td>
                        <td>{{ $account->email }}</td>
                        <td>{{ ucfirst($account->role) }}</td>
                        <td>
                            <div class="records-row">
                                @if($account->role === 'admin' && !\App\Support\SystemOwner::is($account))<button type="button" class="records-button" onclick="document.getElementById('edit-{{ $account->id }}').hidden = !document.getElementById('edit-{{ $account->id }}').hidden">Edit</button>@endif
                                @if(!\App\Support\SystemOwner::is($account))<button type="button" class="records-button" onclick="document.getElementById('password-{{ $account->id }}').hidden = !document.getElementById('password-{{ $account->id }}').hidden">Reset Password</button>@endif
                            </div>
                        </td>
                    </tr>
                    @if($account->role === 'admin' && !\App\Support\SystemOwner::is($account))
                    <tr id="edit-{{ $account->id }}" hidden><td colspan="7">
                        <form method="POST" action="{{ route('admin.users.admins.update',$account->id) }}" class="records-row">
                            @csrf @method('PUT')
                            <label>Name <input class="records-input" name="name" required maxlength="255" value="{{ $account->name }}"></label>
                            <label>Email <input class="records-input" type="email" name="email" required maxlength="255" value="{{ $account->email }}"></label>
                            <button class="submit" type="submit">Save Administrator</button>
                        </form>
                    </td></tr>
                    @endif
                    @if(!\App\Support\SystemOwner::is($account))<tr id="password-{{ $account->id }}" hidden><td colspan="7">
                        <form method="POST" action="{{ route('admin.users.password',$account->id) }}" class="records-row">
                            @csrf
                            <strong>New password for {{ $account->name }}</strong>
                            <label>Password <input class="records-input" type="password" name="password" minlength="12" autocomplete="new-password" required></label>
                            <label>Confirm password <input class="records-input" type="password" name="password_confirmation" minlength="12" autocomplete="new-password" required></label>
                            <button class="submit" type="submit">Update Password</button>
                        </form>
                    </td></tr>@endif
                @empty
                    <tr><td colspan="7">No accounts matched your search.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    <div class="records-pagination">{{ $accounts->links() }}</div>
</div>
@endsection
