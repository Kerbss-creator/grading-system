@extends('layouts.admin')
@section('title', 'CSV Import - '.ucfirst($type))
@section('header', ucfirst($type).' CSV Import')
@section('header-description', 'Preview every change before applying it to the database.')
@section('page-css')
<link rel="stylesheet" href="{{ asset('css/admin/csv.css') }}">@endsection
@section('content')
<div class="records-panel">
    <h2>Upload {{ ucfirst($type) }} CSV</h2>
    <p class="subtle">Use the template headers in this exact format. No records are changed during preview. Up to 1,000 rows and 2 MB. Passwords cannot be imported or exported in CSV.</p>
    <p class="records-small"><strong>Required headers:</strong> {{ implode(', ', $columns) }}</p>
    <form id="csvUploadForm" action="{{ route('admin.imports.preview', $type) }}" method="POST" enctype="multipart/form-data" class="records-row">
        @csrf
        <input type="file" name="file" accept=".csv,text/csv" required>
        <button class="submit" type="submit">Preview CSV</button>
        <a class="records-link" href="{{ route('admin.exports.csv', $type) }}">Export current {{ $type }} records</a>
    </form>
    @if(session('success'))<div class="records-message">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="records-message records-error">@foreach($errors->all() as $message)<p>{{ $message }}</p>@endforeach</div>@endif
</div>
@if($preview)
<div class="records-panel">
    <h2>Import preview — {{ count($preview) }} rows</h2>
    <p class="subtle">Select the rows you want to import. Database records are only updated after you choose the update option. Conflicts and unchanged records cannot be selected.</p>
    <div class="records-row records-summary">
        <span>New: {{ collect($preview)->where('state','new')->count() }}</span>
        <span>Changed: {{ collect($preview)->where('state','changed')->count() }}</span>
        <span>Unchanged: {{ collect($preview)->where('state','unchanged')->count() }}</span>
        <span>Conflicts: {{ collect($preview)->where('state','conflict')->count() }}</span>
    </div>

    <form action="{{ route('admin.imports.confirm', $type) }}" method="POST" id="csvConfirmForm">

        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="records-row">
            <label for="mode">Action:</label>
            <select name="mode" id="mode">
                <option value="add_only">Add new records only</option>
                <option value="add_and_update">Add new and update selected existing records</option>
            </select>
        </div>
        <div class="records-scroll">
            <table class="records-data-table">
                <thead>
                    <tr>
                        <th>Apply</th>
                        <th>CSV line</th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Result</th>
                        <th>Discrepancies / errors</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($preview as $row)
                    <tr class="{{ $row['state'] }}">
                        <td>@if(in_array($row['state'],['new','changed']))<input class="csv-row" type="checkbox" name="lines[]" value="{{ $row['line'] }}" data-state="{{ $row['state'] }}" {{ $row['state'] === 'new' ? 'checked' : 'disabled' }}>@endif</td>
                        <td>{{ $row['line'] }}</td>
                        <td>{{ $row['data'][$type === 'students' ? 'student_id' : ($type === 'teachers' ? 'teacher_id' : 'admin_id')] ?? '—' }}</td>
                        <td>{{ $type === 'admins' ? ($row['data']['name'] ?? '') : trim(($row['data']['first_name'] ?? '').' '.($row['data']['last_name'] ?? '')) }}</td>
                        <td>{{ $row['data']['email'] ?? '—' }}</td>
                        <td><span class="state-{{ $row['state'] }}">{{ ucfirst($row['state']) }}</span></td>
                        <td class="records-details">
                            @foreach($row['issues'] as $msg)<p class="records-small">{{ $msg }}</p>@endforeach
                            @if($row['differences'])<details>
                                <summary>See {{ count($row['differences']) }} differences</summary>@foreach($row['differences'] as $diff)<p class="records-small">{{ $diff }}</p>@endforeach
                            </details>@endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="records-row">
            <button class="submit" type="submit" id="confirmImport">Import selected records</button>
            <span class="records-small">Only selected eligible rows will be written. Missing CSV rows never delete database records. New admin accounts require a separate password setup in User Management.</span>
        </div>
    </form>
</div>
@endif
<div id="csvProcessingModal" class="csv-processing-overlay" role="status" aria-live="polite" aria-label="CSV processing" hidden>
    <div class="csv-processing-card">
        <div class="csv-loading-spinner" aria-hidden="true"></div>
        <h2 id="csvProcessingHeading">Processing CSV...</h2>
        <p id="csvProcessingMessage">Your file is being processed. Please do not submit again.</p>
    </div>
</div>
@endsection
@section('page-js')
<script>
    const busyModal = document.querySelector('#csvProcessingModal');

    function showCsvProcessing(title, description) {
        document.querySelector('#csvProcessingHeading').textContent = title;
        document.querySelector('#csvProcessingMessage').textContent = description;
        busyModal.hidden = false;
    }
    const uploadCsvForm = document.querySelector('#csvUploadForm');
    uploadCsvForm?.addEventListener('submit', () => {
        if (!uploadCsvForm.checkValidity()) return;
        showCsvProcessing('Checking CSV file...', 'Uploading and validating records. The preview will appear when processing finishes.');
        uploadCsvForm.querySelector('button[type="submit"]').disabled = true;
    });
    const mode = document.querySelector('#mode');
    const rows = document.querySelectorAll('.csv-row');

    function changeMode() {
        rows.forEach(box => {
            if (box.dataset.state === 'changed') {
                box.disabled = mode.value !== 'add_and_update';
                if (box.disabled) box.checked = false;
            }
        });
    }
    mode?.addEventListener('change', changeMode);
    changeMode();
    const csvConfirmForm = document.querySelector('#csvConfirmForm');
    let csvApproved = false;
    csvConfirmForm?.addEventListener('submit', async e => {
        if (csvApproved) {
            showCsvProcessing('Importing selected records...', 'Saving selected records to the database. Please do not submit again.');
            document.querySelector('#confirmImport').disabled = true;
            return;
        }
        e.preventDefault();
        const selected = [...rows].filter(b => b.checked && !b.disabled).length;
        if (!selected) {
            await showModal('Select at least one valid record to import.', 'warning');
            return;
        }
        if (await showConfirm(`Import ${selected} selected records? The change will be saved in the activity log.`)) {
            csvApproved = true;
            csvConfirmForm.requestSubmit(e.submitter || undefined);
        }
    });
</script>
@endsection