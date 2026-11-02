@extends('layouts.admin')
@section('title','Student Academic Record')
@section('header',$student->first_name.' '.$student->last_name)
@section('header-description','Student academic record · '.$student->student_id)
@section('page-css')<link rel="stylesheet" href="{{asset('css/admin/academic-records.css')}}">@endsection
@section('content')
<div class="ac-wrap">
  <a class="ac-btn secondary" href="{{route('admin.students.index')}}" style="width:max-content">Back to Student Management</a>
  <section class="ac-panel"><h2>Student information</h2>
    <div class="ac-grid">
      <div><span class="ac-muted">Student ID</span><p>{{ $student->student_id }}</p></div>
      <div><span class="ac-muted">Grade & section</span><p>{{ $student->grade_level }} · {{ $student->section ?: 'Not assigned' }}</p></div>
      <div><span class="ac-muted">School year</span><p>{{ $student->school_year }}</p></div>
      <div><span class="ac-muted">Email</span><p>{{ $student->email ?: 'Not set' }}</p></div>
      <div><span class="ac-muted">Enrollment status</span><p>{{ ucfirst($student->status) }}</p></div>
      <div><span class="ac-muted">Class adviser</span><p>{{ $adviser?->teacher ? $adviser->teacher->first_name.' '.$adviser->teacher->last_name : 'Not assigned' }}</p></div>
      <div><span class="ac-muted">Login username</span><p>{{ $student->account?->username ?: 'Account not linked' }}</p></div>
    </div>
  </section>
  <section class="ac-panel"><h2>Assigned subjects · Teachers · Timetable</h2>
    <div class="ac-table-wrap"><table class="ac-table"><thead><tr><th>Subject</th><th>Class</th><th>Teacher / adviser</th><th>Days</th><th>Time</th><th>Room</th></tr></thead><tbody>
      @forelse($offerings as $offering)<tr><td><a href="{{route('admin.subjects.show',$offering->subject)}}">{{$offering->subject->name}}</a></td><td>{{$offering->section}}</td><td>{{$offering->teacher ? $offering->teacher->first_name.' '.$offering->teacher->last_name : 'Unassigned'}}</td><td>{{$offering->meeting_days ?: '—'}}</td><td>{{$offering->starts_at ?: '—'}}–{{$offering->ends_at ?: '—'}}</td><td>{{$offering->room ?: '—'}}</td></tr>
      @empty <tr><td colspan="6">No subject assignments found for this grade, section and school year.</td></tr>@endforelse
    </tbody></table></div>
  </section>
  <section class="ac-panel"><h2>Grades and approval history</h2>
    <div class="ac-toolbar"><a class="ac-btn secondary" href="{{route('admin.grade-approval',['student_id'=>$student->id])}}">View approval queue for this student</a></div>
    <div class="ac-table-wrap"><table class="ac-table"><thead><tr><th>Subject</th><th>School year</th><th>Quarter</th><th>Grade</th><th>Status</th><th>Approved / returned by</th><th>Review note</th></tr></thead><tbody>
      @forelse($grades as $grade)<tr><td>{{$grade->offering->subject->name}}</td><td>{{$grade->offering->subject->school_year}}</td><td>Quarter {{$grade->quarter}}</td><td>{{$grade->grade ?? '—'}}</td><td><span class="ac-status {{$grade->status}}">{{$grade->status}}</span></td><td>{{$grade->reviewer?->name ?? '—'}}</td><td>{{$grade->review_note ?: '—'}}</td></tr>
      @empty<tr><td colspan="7">No saved grades yet. The system does not manufacture grades from enrolment data.</td></tr>@endforelse
    </tbody></table></div>
  </section>
</div>
@endsection
