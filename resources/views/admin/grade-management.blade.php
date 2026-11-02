@extends($layout)
@section('title','Grade Management')
@section('header','Grade Management')
@section('header-description','Real grade records linked to students, classes and subjects.')
@section('page-css')<link rel="stylesheet" href="{{asset('css/admin/academic-records.css')}}">@endsection
@section('content')
<div class="ac-wrap">
  @if(session('success'))<div class="ac-feedback">{{session('success')}}</div>@endif
  @if($errors->any())<div class="ac-error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
  <section class="ac-panel"><h2>Choose your class and quarter</h2>
    <form method="GET" class="ac-toolbar" action="{{ $isTeacher ? route('teacher.grades') : route('admin.grades') }}">
      <label>Class/subject <select name="offering_id" required>@foreach($offerings as $offering)<option value="{{ $offering->id }}" @selected($selected?->id===$offering->id)>{{ $offering->subject->name }} · {{ $offering->subject->grade_level }} / {{ $offering->section }} · {{ $offering->subject->school_year }}</option>@endforeach</select></label>
      <label>Grading period<select name="quarter">@foreach([1,2,3,4] as $q)<option value="{{$q}}" @selected($quarter===$q)>Quarter {{$q}}</option>@endforeach</select></label>
      <button class="ac-btn">Show gradebook</button>
    </form>
    @if(!$selected)<p class="ac-note">No class/subject assignment exists yet. An administrator must first create a subject and assign its teacher and class under Subject Management.</p>@else
      <p class="ac-muted">{{ $selected->subject->grade_level }} · {{ $selected->section }} · {{ $selected->subject->school_year }} · {{ $selected->teacher ? $selected->teacher->first_name.' '.$selected->teacher->last_name : 'No teacher assigned' }}</p>
    @endif
  </section>
  @if($selected)
  <section class="ac-panel"><h2>Gradebook — Quarter {{ $quarter }}</h2>
    <p class="ac-note">Save grades as drafts first. Select completed draft/returned grades and submit them for administrative review. Pending and approved grades are locked against editing.</p>
    <form method="POST" id="submit-grades" action="{{ $isTeacher ? route('teacher.grades.submit') : route('admin.grades.submit') }}">@csrf</form>
    <div class="ac-table-wrap"><table class="ac-table"><thead><tr><th><input type="checkbox" id="selectDraftAll" aria-label="Select all submittable drafts"> Submit</th><th>Student ID</th><th>Student</th><th>Grade (0–100)</th><th>Status</th><th>Action</th><th>Reviewer note</th></tr></thead><tbody>
      @forelse($students as $student)
        @php($record = $grades->get($student->id))
        @php($editable = !$record || in_array($record->status,['draft','returned'],true))
        <tr>
          <td>@if($record && $editable && $record->grade !== null)<input type="checkbox" form="submit-grades" name="ids[]" value="{{ $record->id }}" aria-label="Submit grade for {{ $student->first_name }} {{ $student->last_name }}">@endif</td>
          <td>{{ $student->student_id }}</td><td>{{ $student->first_name }} {{ $student->last_name }}</td>
          <td><input form="grade-{{ $student->id }}" name="grade" class="ac-mini" type="number" min="0" max="100" step="0.01" value="{{ $record?->grade }}" @disabled(!$editable) required></td>
          <td><span class="ac-status {{ $record?->status ?? 'draft' }}">{{ $record?->status ?? 'not saved' }}</span></td>
          <td>@if($editable)<form id="grade-{{ $student->id }}" method="POST" action="{{ $isTeacher ? route('teacher.grades.save',$selected) : route('admin.grades.save',$selected) }}">@csrf<input type="hidden" name="student_id" value="{{ $student->id }}"><input type="hidden" name="quarter" value="{{ $quarter }}"><button class="ac-btn secondary" type="submit">Save draft</button></form>@else Locked @endif</td>
          <td>{{ $record?->review_note ?: '—' }}</td>
        </tr>
      @empty<tr><td colspan="7">No enrolled students match this class. Confirm grade level, school year, and section.</td></tr>@endforelse
    </tbody></table></div>
    <div class="ac-footer"><button class="ac-btn" type="submit" form="submit-grades">Submit selected drafts</button><span class="ac-muted">Submitted grades become pending; the administrator can approve or return them.</span></div>
  </section>
  @endif
</div>
<script>document.getElementById('selectDraftAll')?.addEventListener('change', function () { document.querySelectorAll('input[form="submit-grades"][name="ids[]"]').forEach(box => box.checked = this.checked); });</script>
@endsection
