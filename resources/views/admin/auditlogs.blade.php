@extends('layouts.admin')

@section('title', 'Audit Logs')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/audit-logs.css') }}">
@endsection

@section('content')
    <section class="audit-page">

        {{-- PAGE HEADER --}}
        <div class="audit-header">
            <div>
                <h1>Audit Logs</h1>
                <p>Track important activities and changes in the system.</p>
            </div>
        </div>

        {{-- SUMMARY CARDS --}}
        <div class="audit-stats">
            <div class="audit-stat-card">
                <div class="audit-stat-icon">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <span>Total Logs</span>
                    <h2>{{ number_format($totalLogs) }}</h2>
                </div>
            </div>

            <div class="audit-stat-card">
                <div class="audit-stat-icon">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
                <div>
                    <span>Today's Activities</span>
                    <h2>{{ number_format($todayLogs) }}</h2>
                </div>
            </div>

            <div class="audit-stat-card">
                <div class="audit-stat-icon">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <span>Users Tracked</span>
                    <h2>{{ number_format($usersTracked) }}</h2>
                </div>
            </div>
        </div>

        {{-- AUDIT LOGS TABLE --}}
        <div class="audit-panel">

            <div class="audit-panel-header">
                <div>
                    <h2>Activity History</h2>
                    <p>Review recorded system activities.</p>
                </div>
            </div>

            {{-- SEARCH AND FILTERS --}}
            <form method="GET" action="{{ route('audit-logs.index') }}" class="audit-filters">

                <div class="audit-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" name="search" placeholder="Search user, action, or description..."
                        value="{{ request('search') }}">
                </div>

                <select name="action" aria-label="Filter by action">
                    <option value="">All Actions</option>
                    @foreach (['Create', 'Update', 'Delete', 'Submit', 'Approve', 'Login', 'Logout', 'View'] as $action)
                        <option value="{{ $action }}" @selected(request('action') === $action)>
                            {{ $action }}
                        </option>
                    @endforeach
                </select>

                <select name="module" aria-label="Filter by module">
                    <option value="">All Modules</option>
                    @foreach (['Grades', 'Grade Approval', 'Academic Records', 'User Management', 'Announcements', 'Authentication'] as $module)
                        <option value="{{ $module }}" @selected(request('module') === $module)>
                            {{ $module }}
                        </option>
                    @endforeach
                </select>

                <input type="date" name="from" aria-label="From date" value="{{ request('from') }}">

                <input type="date" name="to" aria-label="To date" value="{{ request('to') }}">

                <button type="submit" class="audit-btn">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>

                <a href="{{ route('audit-logs.index') }}" class="audit-reset">
                    Reset
                </a>
            </form>

            {{-- TABLE --}}
            <div class="audit-table-wrapper">
                <table class="audit-table">
                    <thead>
                        <tr>
                            <th>Date &amp; Time</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Description</th>
                            <th>Details</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="audit-date">
                                    {{ $log->created_at?->format('M d, Y') }}
                                    <small>
                                        {{ $log->created_at?->format('h:i A') }}
                                    </small>
                                </td>

                                <td>{{ $log->user_name ?? 'Unknown User' }}</td>

                                <td>
                                    {{ $log->user_role ?? 'Unknown' }}
                                </td>

                                <td>
                                    <span class="audit-badge
                                            {{ strtolower($log->action) }}">
                                        {{ $log->action }}
                                    </span>
                                </td>

                                <td>{{ $log->module }}</td>

                                <td class="audit-description">
                                    {{ $log->description }}
                                </td>

                                <td>
                                    <button type="button" class="audit-view-btn"
                                        data-url="{{ route('audit-logs.show', $log) }}">
                                        <i class="fa-regular fa-eye"></i>
                                        View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="audit-empty">
                                        <i class="fa-regular fa-folder-open"></i>
                                        <h3>No Audit Logs Yet</h3>
                                        <p>
                                            Activities will appear here when
                                            they are recorded by the system.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="audit-pagination">
                <span>
                    Showing {{ $logs->firstItem() ?? 0 }}
                    to {{ $logs->lastItem() ?? 0 }}
                    of {{ $logs->total() }} entries
                </span>

                {{ $logs->links() }}
            </div>
        </div>
    </section>

    {{-- DETAILS MODAL --}}
    <div class="audit-modal-overlay" id="auditModal" hidden>
        <div class="audit-modal" role="dialog" aria-modal="true" aria-labelledby="auditModalTitle">

            <div class="audit-modal-header">
                <h2 id="auditModalTitle">Audit Log Details</h2>
                <button type="button" id="closeAuditModal" class="audit-close" aria-label="Close">
                    &times;
                </button>
            </div>

            <div class="audit-modal-body" id="auditModalBody">
                Loading details...
            </div>

            <div class="audit-modal-footer">
                <button type="button" id="closeAuditModalBottom" class="audit-btn">
                    Close
                </button>
            </div>
        </div>
    </div>
@endsection

@section('page-js')
    <script src="{{ asset('js/admin/audit-logs.js') }}" defer></script>
@endsection