@extends('layouts.admin')
@section('title', 'Administrator Management')
@section('header', 'Administrator Management')
@section('header-description', 'Manage administrator accounts and permissions.')
@section('page-css')
<link rel="stylesheet" href="{{ asset('css/admin/records-data.css') }}">
@endsection
@section('content')
<div class="records-panel">
    <div class="records-row records-heading-actions">
        <h2>Administrators</h2>
        <a class="records-button" href="{{ route('admin.users') }}">All accounts</a>
        <a class="records-button" href="{{ route('admin.imports.index', 'admins') }}">Import CSV</a>
        <a class="records-button" href="{{ route('admin.exports.csv', 'admins') }}">Export CSV</a>
    </div>
    <form class="records-row records-filter-form" action="{{ route('admin.admins.index') }}" method="GET">
        <label>Search administrators <input class="records-input" name="search" placeholder="Name or email" value="{{ request('search') }}"></label>
        <button class="submit" type="submit">Search</button>
        <a class="records-button" href="{{ route('admin.admins.index') }}">Clear</a>
    </form>
</div>
<div class="records-panel">
    <h2>Add Administrator</h2>
    <form method="POST" action="{{ route('admin.users.admins.store') }}" class="records-row records-form-grid">
        @csrf
        <label>Name <input class="records-input" name="name" required maxlength="255" value="{{ old('name') }}"></label>
        <label>Email <input class="records-input" type="email" name="email" required maxlength="255" value="{{ old('email') }}"></label>
        <label>Initial password <input class="records-input" type="password" name="password" minlength="12" autocomplete="new-password" required></label>
        <label>Confirm password <input class="records-input" type="password" name="password_confirmation" minlength="12" autocomplete="new-password" required></label>
        <button class="submit" type="submit">Create Administrator</button>
    </form>
</div>
<div class="records-panel">
    <form id="removeAdministratorsForm" method="POST" action="{{ route('admin.users.admins.bulkDestroy') }}" data-confirm="Move selected administrator accounts to Recycle Bin? Their logins will be blocked.">
        @csrf @method('DELETE')
    </form>
    <div class="records-row records-heading-actions">
        <h2>Registered Administrators</h2>
        <button type="button" class="records-button" data-admin-status="active">Activate Selected</button>
        <button type="button" class="records-button" data-admin-status="inactive">Deactivate Selected</button>
        <button type="submit" class="records-button records-danger" form="removeAdministratorsForm">Delete Selected</button>
    </div>
    <p class="records-small">Your account and the protected system account cannot be selected. Passwords cannot be displayed; use Reset Password to change one.</p>
    <div class="records-scroll">
        <table class="records-data-table">
            <thead><tr><th>Select</th><th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($accounts as $account)
                    <tr>
                        <td>@if($account->id !== auth()->id() && !\App\Support\SystemOwner::is($account))<input type="checkbox" name="ids[]" form="removeAdministratorsForm" value="{{ $account->id }}" aria-label="Select {{ $account->name }}">@endif</td>
                        <td>{{ \App\Support\SystemOwner::is($account) ? '404' : $account->id }}</td>
                        <td>{{ $account->name }}</td><td><code>{{ $account->username ?? "—" }}</code></td><td>{{ $account->email }}</td><td>{{ $account->is_active ? 'Active' : 'Inactive' }}</td>
                        <td>
                            @if(!\App\Support\SystemOwner::is($account))
                                <button type="button" class="records-button" onclick="document.getElementById('edit-admin-{{ $account->id }}').hidden = !document.getElementById('edit-admin-{{ $account->id }}').hidden">Edit</button>
                                <button type="button" class="records-button" onclick="document.getElementById('reset-admin-{{ $account->id }}').hidden = !document.getElementById('reset-admin-{{ $account->id }}').hidden">Reset Password</button>
                            @endif
                        </td>
                    </tr>
                    @if(!\App\Support\SystemOwner::is($account))
                    <tr id="edit-admin-{{ $account->id }}" hidden><td colspan="7"><form method="POST" class="records-row" action="{{ route('admin.users.admins.update', $account->id) }}">@csrf @method('PUT')<label>Name<input class="records-input" name="name" maxlength="255" required value="{{ $account->name }}"></label><label>Email<input class="records-input" type="email" name="email" required maxlength="255" value="{{ $account->email }}"></label><button type="submit" class="submit">Save</button></form></td></tr>
                    <tr id="reset-admin-{{ $account->id }}" hidden><td colspan="7"><form method="POST" class="records-row" action="{{ route('admin.users.password', $account->id) }}">@csrf<label>New password<input class="records-input" name="password" type="password" minlength="12" autocomplete="new-password" required></label><label>Confirm password<input class="records-input" name="password_confirmation" type="password" minlength="12" autocomplete="new-password" required></label><button type="submit" class="submit">Update Password</button></form></td></tr>
                    @endif
                @empty
                    <tr><td colspan="7">No administrators found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="records-pagination">{{ $accounts->links() }}</div>
</div>
@endsection

@section('page-js')
<script>
document.querySelectorAll('[data-admin-status]').forEach(button => button.addEventListener('click', async () => {
    const ids = [...document.querySelectorAll('#removeAdministratorsForm input[name="ids[]"]:checked, input[form="removeAdministratorsForm"][name="ids[]"]:checked')].map(input => Number(input.value));
    if (!ids.length) { await showModal('Select at least one administrator.', 'warning'); return; }
    const status = button.dataset.adminStatus;
    if (!await showConfirm(`Set ${ids.length} selected administrator account(s) to ${status}?`)) return;
    const controls = document.querySelectorAll('[data-admin-status]');
    controls.forEach(control => control.disabled = true);
    try {
        const response = await fetch(@json(route('admin.users.admins.bulkStatus')), {
            method: 'PATCH',
            headers: {'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content || ''},
            body: JSON.stringify({ids,status}),
        });
        const body = await response.json();
        if (!response.ok) throw new Error(body.message || Object.values(body.errors || {}).flat().join('\n') || 'Could not update administrators.');
        await showModal(body.message, 'success');
        window.location.reload();
    } catch (error) {
        await showModal(error.message, 'error');
        controls.forEach(control => control.disabled = false);
    }
}));
</script>
@endsection
