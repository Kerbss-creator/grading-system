@extends('layouts.admin')
@section('title','Activity Log')
@section('header','Activity Log')
@section('header-description','Shared activity history for school administrators.')
@section('page-css')<link rel="stylesheet" href="{{ asset('css/admin/records-data.css') }}">@endsection
@section('content')
<div class="records-panel">
    <form method="GET" action="{{ route('admin.activity-log') }}" class="records-row">
        <label>Record type <select name="type"><option value="">All</option>@foreach(['students','teachers','admins','users'] as $value)<option value="{{ $value }}" @selected(request('type') === $value)>{{ ucfirst($value) }}</option>@endforeach</select></label>
        <label>Action <select name="action"><option value="">All</option>@foreach(['create','update','delete','restore','status_change','csv_import','csv_export','password_reset','password_reset'] as $value)<option value="{{ $value }}" @selected(request('action') === $value)>{{ ucwords(str_replace('_',' ',$value)) }}</option>@endforeach</select></label>
        <button class="submit" type="submit">Filter</button>
    </form>
    <p class="subtle">Times shown in {{ config('app.timezone') }}. Stored actor information is a snapshot at the time of action. Student, teacher, administrator, account password changes and CSV actions are logged. Never log passwords.</p>
    <div class="records-scroll"><table class="records-data-table"><thead><tr><th>Date &amp; time</th><th>Performed by</th><th>Action</th><th>Type</th><th>Record</th><th>Changes</th></tr></thead><tbody>
    @forelse($logs as $log)
    <tr>
        <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
        <td>{{ $log->actor_name }}<p class="records-small">{{ $log->actor_email }}</p></td>
        <td>{{ ucwords(str_replace('_',' ',$log->action)) }}</td><td>{{ ucfirst($log->record_type) }}</td><td>{{ $log->record_key ?? '—' }}</td>
        <td class="records-details"><details><summary>View details</summary>
            @if($log->before)<p class="records-small"><strong>Before:</strong> {{ json_encode($log->before, JSON_UNESCAPED_UNICODE) }}</p>@endif
            @if($log->after)<p class="records-small"><strong>After:</strong> {{ json_encode($log->after, JSON_UNESCAPED_UNICODE) }}</p>@endif
            @if($log->details)<p class="records-small"><strong>Operation:</strong> {{ json_encode($log->details, JSON_UNESCAPED_UNICODE) }}</p>@endif
        </details></td>
    </tr>
    @empty<tr><td colspan="6">No matching activity yet.</td></tr>@endforelse
    </tbody></table></div>
    <div class="records-pagination">{{ $logs->links() }}</div>
</div>
@endsection
