@extends('layouts.admin')

@section('title', 'Audit Logs')

@section('header')
    <span class="audit-header-title">Audit Logs</span>
@endsection

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/admin/audit-logs.css') }}">
@endsection

@section('content')

    <section class="audit-page">

        <div class="audit-page-heading">
            <div class="audit-heading-text">
                <div class="audit-eyebrow">
                    <i class="fa-solid fa-shield-halved"></i>
                    SYSTEM MONITORING
                </div>

                <p>Monitor and review activities performed throughout the system.</p>
            </div>

            <div class="audit-heading-icon">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
        </div>

        {{-- SUMMARY CARDS --}}
        <div class="audit-summary-grid">

            <article class="audit-summary-card">
                <div class="audit-summary-top">
                    <span class="audit-summary-label">Total Logs</span>
                    <span class="audit-summary-icon icon-green">
                        <i class="fa-solid fa-list-check"></i>
                    </span>
                </div>

                <h2>{{ number_format($totalActivities) }}</h2>
                <p>All recorded system activities</p>
            </article>

            <article class="audit-summary-card">
                <div class="audit-summary-top">
                    <span class="audit-summary-label">Today's Activities</span>
                    <span class="audit-summary-icon icon-blue">
                        <i class="fa-solid fa-calendar-day"></i>
                    </span>
                </div>

                <h2>{{ number_format($todayLogs) }}</h2>
                <p>Activities recorded today</p>
            </article>

            <article class="audit-summary-card">
                <div class="audit-summary-top">
                    <span class="audit-summary-label">Users Tracked</span>
                    <span class="audit-summary-icon icon-orange">
                        <i class="fa-solid fa-users"></i>
                    </span>
                </div>

                <h2>{{ number_format($activeUsers) }}</h2>
                <p>Users with recorded activities in the last 30 days</p>
            </article>

        </div>

        {{-- ACTIVITY HISTORY --}}
        <div class="audit-content-card">

            <div class="audit-content-heading">
                <div>
                    <h2>Activity History</h2>
                    <p>View, search, and filter recorded system activities.</p>
                </div>

                <div class="audit-record-count">
                    <i class="fa-solid fa-database"></i>
                    {{ number_format($logs->total()) }} Records
                </div>
            </div>

            {{-- SEARCH AND FILTERS --}}
            <form method="GET" action="{{ route('admin.activity-log') }}" class="audit-filter-form" id="auditFilterForm">

                <div class="audit-search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input type="search" name="search" placeholder="Search user, action, or description..."
                        value="{{ request('search') }}">
                </div>

                <select name="action" aria-label="Filter by action">
                    <option value="">All Actions</option>

                    @foreach ($availableActions as $action)
                        <option value="{{ $action }}" @selected(request('action') === $action)>
                            {{ ucwords(str_replace('_', ' ', $action)) }}
                        </option>
                    @endforeach
                </select>

                <select name="type" aria-label="Filter by module">
                    <option value="">All Modules</option>

                    @foreach ($availableTypes as $type)
                        <option value="{{ $type }}" @selected(request('type') === $type)>
                            {{ ucwords(str_replace('_', ' ', $type)) }}
                        </option>
                    @endforeach
                </select>

                <div class="audit-date-filter">
                    <label for="auditFrom">From</label>
                    <input type="date" id="auditFrom" name="from" value="{{ request('from') }}">
                </div>

                <div class="audit-date-filter">
                    <label for="auditTo">To</label>
                    <input type="date" id="auditTo" name="to" value="{{ request('to') }}">
                </div>

                <a href="{{ route('admin.activity-log') }}" class="audit-reset-button">
                    <i class="fa-solid fa-rotate-left"></i>
                    Refresh
                </a>

            </form>

            {{-- AUDIT LOG TABLE --}}
            <div class="audit-table-container">

                <table class="audit-data-table">

                    <thead>
                        <tr>
                            <th>Date &amp; Time</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Description</th>
                            <th class="audit-details-heading">Details</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($logs as $log)

                            <tr>
                                <td class="audit-date-cell">
                                    <span>
                                        {{ $log->created_at?->format('M d, Y') ?? '—' }}
                                    </span>

                                    <small>
                                        {{ $log->created_at?->format('h:i A') ?? '—' }}
                                    </small>
                                </td>

                                <td class="audit-user-cell">
                                    <div class="audit-user-name">
                                        <span class="audit-user-avatar">
                                            <i class="fa-solid fa-user"></i>
                                        </span>

                                        {{ $log->user_name ?? 'Unknown User' }}
                                    </div>
                                </td>

                                <td>
                                    <span class="audit-role-text">
                                        {{ $log->user_role ?? 'Not recorded' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="audit-action-badge {{ strtolower($log->action ?? 'unknown') }}">
                                        {{ ucwords(str_replace('_', ' ', $log->action ?? 'Unknown')) }}
                                    </span>
                                </td>
                                

                                <td class="audit-module-cell">
                                    {{ $log->module ?? '—' }}
                                </td>

                                <td class="audit-description-cell">
                                    {{ $log->description ?? 'No description provided.' }}
                                </td>

                                <td>
                                    <button type="button" class="audit-details-button"
                                        data-url="{{ route('admin.activity-log.show', $log->id) }}">
                                        <i class="fa-regular fa-eye"></i>
                                        View
                                    </button>
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="7">

                                    <div class="audit-empty-state">
                                        <div class="audit-empty-icon">
                                            <i class="fa-regular fa-folder-open"></i>
                                        </div>

                                        <h3>No Audit Logs Found</h3>

                                        <p>
                                            No activities match your current filters.
                                            Recorded system activities will appear here.
                                        </p>
                                    </div>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>
                </table>

            </div>

            {{-- PAGINATION --}}
            <div class="audit-table-footer">

                <span class="audit-pagination-info">
                    Showing {{ $logs->firstItem() ?? 0 }}
                    to {{ $logs->lastItem() ?? 0 }}
                    of {{ $logs->total() }} entries
                </span>

                <div class="audit-pagination-links">
                    {{ $logs->links() }}
                </div>

            </div>

        </div>

    </section>

    {{-- AUDIT DETAILS MODAL --}}
    <div class="audit-modal-overlay" id="auditModal" hidden>

        <div class="audit-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="auditModalTitle">

            <div class="audit-modal-heading">

                <div class="audit-modal-title-group">
                    <span class="audit-modal-icon">
                        <i class="fa-solid fa-clipboard-list"></i>
                    </span>

                    <div>
                        <h2 id="auditModalTitle">Audit Log Details</h2>
                        <p>Complete information about this activity.</p>
                    </div>
                </div>

                <button type="button" id="closeAuditModal" class="audit-modal-close" aria-label="Close modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>

            </div>

            <div class="audit-modal-content" id="auditModalBody">
                <div class="audit-modal-loading">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    Loading details...
                </div>
            </div>

            <div class="audit-modal-actions">
                <button type="button" id="closeAuditModalBottom" class="audit-modal-done">
                    Close
                </button>
            </div>

        </div>

    </div>

@endsection

@section('page-js')
    <script src="{{ asset('js/admin/audit-logs.js') }}" defer></script>
@endsection