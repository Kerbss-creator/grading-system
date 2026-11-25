@extends('layouts.admin')

@section('title', 'Subject Management')
@section('header', 'Subject Management')
@section('header-description', 'View the predefined subjects offered by Forbes Academy.')

@section('content')

  <link rel="stylesheet" href="{{ asset('css/admin/subject-management-admin.css') }}">

  <div class="subject-management">

    {{-- SEARCH AND FILTERS --}}
    <div class="subject-toolbar">

      <div class="subject-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="subjectSearch" placeholder="Search subject..." aria-label="Search subjects">
      </div>

      <div class="subject-filters">

        <select id="gradeFilter" aria-label="Filter by grade level">
          <option value="">All Grade Levels</option>
          <option value="7">Grade 7</option>
          <option value="8">Grade 8</option>
          <option value="9">Grade 9</option>
          <option value="10">Grade 10</option>
        </select>

        <select id="statusFilter" aria-label="Filter by status">
          <option value="">All Statuses</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>

      </div>
    </div>

    
{{-- SUBJECT TABLE --}}
<div class="subject-table-container">

    <table class="subject-table">

        <thead>
            <tr>
                <th>Subject Name</th>
                <th>Grade Levels</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody id="subjectTableBody">

            @forelse($subjects as $subject)

                @php
                    $grades = $subject->gradeLevels
                        ->pluck('grade_level')
                        ->map(fn($grade) => (int) filter_var($grade, FILTER_SANITIZE_NUMBER_INT))
                        ->sort()
                        ->values();

                    $gradeNumbers = $grades->implode(',');
                @endphp

                <tr
                    data-grades="{{ $gradeNumbers }}"
                    data-status="{{ strtolower($subject->status) }}"
                >

                    <td>
                        <div class="subject-name">

                            <span class="subject-icon">
                                @switch(strtolower($subject->subject_name))
                                    @case('mathematics')
                                        <i class="fa-solid fa-calculator"></i>
                                        @break

                                    @case('science')
                                        <i class="fa-solid fa-flask"></i>
                                        @break

                                    @case('computer')
                                        <i class="fa-solid fa-computer"></i>
                                        @break

                                    @case('english')
                                        <i class="fa-solid fa-book-open"></i>
                                        @break

                                    @case('filipino')
                                        <i class="fa-solid fa-language"></i>
                                        @break

                                    @case('araling panlipunan')
                                        <i class="fa-solid fa-earth-asia"></i>
                                        @break

                                    @case('tle')
                                        <i class="fa-solid fa-screwdriver-wrench"></i>
                                        @break

                                    @case('mapeh')
                                        <i class="fa-solid fa-music"></i>
                                        @break

                                    @case('values education')
                                        <i class="fa-solid fa-heart"></i>
                                        @break

                                    @default
                                        <i class="fa-solid fa-book"></i>
                                @endswitch
                            </span>

                            <span>{{ $subject->subject_name }}</span>

                        </div>
                    </td>

                    <td>
                        @if($grades->isNotEmpty())
                            Grades {{ $grades->implode(', ') }}
                        @else
                            —
                        @endif
                    </td>

                    <td>
                        <span class="status-badge {{ strtolower($subject->status) }}">
                            {{ ucfirst($subject->status) }}
                        </span>
                    </td>

                    <td>
                        <button
                            type="button"
                            class="view-btn"
                            data-subject="{{ $subject->subject_name }}"
                            data-code="{{ $subject->subject_code }}"
                            aria-label="View {{ $subject->subject_name }} details"
                            title="View Details"
                        >
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="4">
                        <div class="subject-empty-state">
                            <i class="fa-solid fa-book-open"></i>
                            <h3>No Subjects Found</h3>
                            <p>There are currently no subjects in the database.</p>
                        </div>
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

    {{-- Empty state for search and filters --}}
    <div class="subject-empty-state" id="subjectEmptyState" hidden>
        <i class="fa-solid fa-book-open"></i>
        <h3>No Subjects Found</h3>
        <p>Try changing your search or filter selections.</p>
    </div>

</div>

{{-- Total count --}}
<div class="subject-table-footer">
    <span id="subjectCount">
        Showing {{ $subjects->count() }} subjects
    </span>
</div>


  </div>

@endsection

@section('page-js')
  <script src="{{ asset('js/admin/subject-management-admin.js') }}"></script>
@endsection