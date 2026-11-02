@extends('layouts.admin')
@section('title','Teacher Assignments')
@section('header',$teacher->first_name.' '.$teacher->last_name)
@section('header-description','Teacher academic assignments · '.$teacher->teacher_id)
@section('page-css')<link rel="stylesheet" href="{{asset('css/admin/academic-records.css')}}">@endsection
@section('content')
<div class="ac-wrap">
  <a class="ac-btn secondary" href="{{route('admin.teachers.index')}}" style="width:max-content">Back to Teacher Management</a>
  <section class="ac-panel"><h2>Teacher information</h2>
    <div class="ac-grid"><div><span class="ac-muted">Email</span><p>{{ $teacher->email }}</p></div><div><span class="ac-muted">School year</span><p>{{ $teacher->school_year }}</p></div><div><span class="ac-muted">Legacy subject field</span><p>{{ $teacher->subject }}</p></div><div><span class="ac-muted">Account username</span><p>{{ $teacher->account?->username ?: 'Not linked' }}</p></div></div>
  </section>
  <section class="ac-panel"><h2>Class adviser assignments</h2>
    @forelse($adviserClasses as $class)
      <details class="ac-details"><summary>{{$class->grade_level}} · Section {{$class->section}} · {{$class->school_year}} (Adviser)</summary>
        <p class="ac-muted">Class adviser for {{$class->studentsQuery()->count()}} enrolled students.</p>
      </details>
    @empty<p class="ac-muted">No adviser assignment has been saved for this teacher.</p>@endforelse
  </section>
  <section class="ac-panel"><h2>Assigned subjects and classes</h2><p class="ac-muted">Expand a class to see its enrolled students without filling the page with every roster.</p>
    @forelse($offerings as $offering)
      <details class="ac-details"><summary>{{ $offering->subject->name }} · {{ $offering->subject->grade_level }} · Section {{ $offering->section }} · {{ $rosters[$offering->id]->count() }} students</summary>
        <p class="ac-muted">{{ $offering->subject->school_year }} · {{ $offering->meeting_days ?: 'Days not set' }} · {{ $offering->starts_at ?: '—' }}–{{ $offering->ends_at ?: '—' }} · Room: {{ $offering->room ?: 'Not set' }}</p>
        <a href="{{route('admin.grades',['offering_id'=>$offering->id])}}" class="ac-btn">Open gradebook</a>
        <div class="ac-table-wrap"><table class="ac-table"><thead><tr><th>ID</th><th>Student</th></tr></thead><tbody>
        @forelse($rosters[$offering->id] as $student)<tr><td>{{$student->student_id}}</td><td><a href="{{route('admin.students.show',$student)}}">{{ $student->first_name }} {{ $student->last_name }}</a></td></tr>
        @empty<tr><td colspan="2">No students assigned to this class.</td></tr>@endforelse
        </tbody></table></div>
      </details>
    @empty<p class="ac-muted">No linked subject or classes assigned. Create them in Subject Management; the legacy free-text subject is not an enrolment.</p>@endforelse
  </section>
</div>
@endsection
