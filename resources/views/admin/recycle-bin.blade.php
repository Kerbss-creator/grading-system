@extends('layouts.admin')
@section('title','Recycle Bin')
@section('header','Recycle Bin')
@section('header-description','Restore student, teacher, and administrator accounts.')
@section('page-css')<link rel="stylesheet" href="{{ asset('css/admin/records-data.css') }}">@endsection
@section('content')
@foreach(['students' => $students, 'teachers' => $teachers, 'admins' => $admins, 'users' => $users] as $type => $records)
<div class="records-panel">
    <h2>{{ ucfirst($type) }} ({{ $records->count() }})</h2>
    <p class="subtle">Deleted records are hidden from normal management screens. Restoring also reactivates linked student and teacher login accounts.</p>
    <div class="records-scroll"><table class="records-data-table"><thead><tr><th>ID</th><th>Name</th><th>Deleted at</th><th>Action</th></tr></thead><tbody>
    @forelse($records as $record)
        <tr><td>{{ in_array($type,['admins','users'],true) ? $record->id : $record->{$type === 'students' ? 'student_id' : 'teacher_id'} }}</td><td>{{ in_array($type,['admins','users'],true) ? $record->name : trim($record->first_name.' '.$record->last_name) }}</td><td>{{ $record->deleted_at }}</td><td><form action="{{ route('admin.recycle-bin.restore', [$type, $record->id]) }}" method="POST" data-confirm="Restore this record and its login account?">@csrf<button class="submit" type="submit">Restore</button></form></td></tr>
    @empty<tr><td colspan="4">No deleted {{ $type }}.</td></tr>@endforelse
    </tbody></table></div>
</div>
@endforeach
@if(session('success'))<div class="records-message">{{ session('success') }}</div>@endif
@if($errors->any())<div class="records-message records-error">@foreach($errors->all() as $message)<p>{{ $message }}</p>@endforeach</div>@endif
@endsection
