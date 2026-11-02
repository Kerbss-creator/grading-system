@extends('layouts.admin')
@section('title','Subject Details')
@section('header',$subject->name)
@section('header-description',$subject->grade_level.' · '.$subject->school_year)
@section('page-css')<link rel="stylesheet" href="{{asset('css/admin/academic-records.css')}}">@endsection
@section('content')
<div class="ac-wrap">
  <a href="{{route('admin.subjects')}}" class="ac-btn secondary" style="width:max-content">Back to subjects</a>
  @if(session('success'))<div class="ac-feedback">{{session('success')}}</div>@endif
  @if($errors->any())<div class="ac-error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
  <section class="ac-panel"><h2>Edit subject</h2>
    <form class="ac-form" method="POST" action="{{route('admin.subjects.update',$subject)}}">@csrf @method('PATCH')
      <label>Subject name<input name="name" required value="{{$subject->name}}" maxlength="150"></label>
      <label>Status<select name="is_active"><option value="1" @selected($subject->is_active)>Active</option><option value="0" @selected(!$subject->is_active)>Inactive</option></select></label>
      <button class="ac-btn">Save subject</button>
    </form>
  </section>
  <section class="ac-panel"><h2>Add subject to a class / grade</h2>
    <p class="ac-note">Choose <strong>ALL</strong> only when the subject really applies to every student in this grade and school year. Otherwise provide an existing section exactly as shown on student records. Records with blank sections are not silently assigned to a class.</p>
    <form method="POST" action="{{route('admin.offerings.store',$subject)}}" class="ac-form">@csrf
      <label>Section<input name="section" value="{{old('section')}}" maxlength="60" placeholder="A, Rizal, or ALL" required></label>
      <label>Teacher<select name="teacher_id"><option value="">Unassigned</option>@foreach($teachers as $teacher)<option value="{{$teacher->id}}" @selected(old('teacher_id')==$teacher->id)>{{ $teacher->first_name }} {{ $teacher->last_name }} ({{ $teacher->teacher_id }})</option>@endforeach</select></label>
      <label>Meeting days<input name="meeting_days" value="{{old('meeting_days')}}" placeholder="Mon, Wed, Fri" maxlength="90"></label>
      <label>Start<input type="time" name="starts_at" value="{{old('starts_at')}}"></label>
      <label>End<input type="time" name="ends_at" value="{{old('ends_at')}}"></label>
      <label>Room<input name="room" value="{{old('room')}}" maxlength="90"></label>
      <button class="ac-btn" type="submit">Assign subject</button>
    </form>
  </section>
  <section class="ac-panel"><h2>Assigned classes, teachers and students</h2>
    @forelse($subject->offerings as $offering)
      <details class="ac-details"><summary>Section {{ $offering->section }} · {{ $offering->teacher ? $offering->teacher->first_name.' '.$offering->teacher->last_name : 'No teacher assigned' }} · {{ $enrolments[$offering->id]->count() }} students</summary>
        <p class="ac-muted">{{ $offering->meeting_days ?: 'Days not set' }} · {{ $offering->starts_at ?: 'Time not set' }} – {{ $offering->ends_at ?: 'Time not set' }} · {{ $offering->room ?: 'Room not set' }}</p>
        <a class="ac-btn" href="{{route('admin.grades',['offering_id'=>$offering->id])}}">Open gradebook</a>
        <details class="ac-details"><summary>Edit teacher or schedule</summary>
          <form class="ac-form" method="POST" action="{{route('admin.offerings.update',$offering)}}">@csrf @method('PATCH')
            <label>Teacher<select name="teacher_id"><option value="">Unassigned</option>@foreach($teachers as $teacher)<option value="{{$teacher->id}}" @selected($teacher->id===$offering->teacher_id)>{{$teacher->first_name}} {{$teacher->last_name}}</option>@endforeach</select></label>
            <label>Days<input name="meeting_days" value="{{$offering->meeting_days}}" placeholder="Mon,Wed,Fri"></label>
            <label>Start<input type="time" name="starts_at" value="{{substr($offering->starts_at ?? '',0,5)}}"></label>
            <label>End<input type="time" name="ends_at" value="{{substr($offering->ends_at ?? '',0,5)}}"></label>
            <label>Room<input name="room" value="{{$offering->room}}"></label>
            <button class="ac-btn" type="submit">Save assignment</button>
          </form>
        </details>
        <div class="ac-table-wrap"><table class="ac-table"><thead><tr><th>ID</th><th>Student</th><th>Status</th></tr></thead><tbody>
          @forelse($enrolments[$offering->id] as $student)<tr><td>{{ $student->student_id }}</td><td><a href="{{route('admin.students.show',$student)}}">{{ $student->first_name }} {{ $student->last_name }}</a></td><td>{{ucfirst($student->status)}}</td></tr>
          @empty <tr><td colspan="3">No matching students. Review grade, section, and school year.</td></tr> @endforelse
        </tbody></table></div>
      </details>
    @empty <p class="ac-muted">No classes assigned to this subject yet.</p> @endforelse
  </section>
</div>
@endsection
