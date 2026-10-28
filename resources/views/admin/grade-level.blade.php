@extends('layouts.admin')

@section('title', 'Grade Level Management')
@section('header', 'Grade Level Management')
@section('header-description', 'View grade levels, assigned advisers, subjects, and academic records.')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('css/admin/grade-level-admin.css') }}">
@endsection

@section('content')
  {{-- GRADE LEVEL DIRECTORY --}}
  <div id="gradeLevelDirectory">

    {{-- SUMMARY CARDS --}}
    <div class="summary-container">
      <div class="summary-card">
        <div class="summary-icon">
          <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <div>
          <span>Total Grade Levels</span>
          <h2>4</h2>
          <small>Grades 7-10</small>
        </div>
      </div>

      <div class="summary-card">
        <div class="summary-icon">
          <i class="fa-solid fa-calendar-days"></i>
        </div>
        <div>
          <span>School Year</span>
          <h2 class="school-year-value">2026-2027</h2>
          <small>Junior High School</small>
        </div>
      </div>
    </div>

    {{-- GRADE LEVEL HEADER --}}
    <div class="section-header">
      <div>
        <h2>Grade Levels</h2>
        <p>
          Select a grade level to view its adviser, subject assignments,
          and learners' academic records.
        </p>
      </div>

      <div class="grade-level-badge">
        <i class="fa-solid fa-school"></i>
        Junior High School
      </div>
    </div>

    {{-- GRADE LEVEL CARDS --}}
    <div class="grade-grid" id="gradeGrid">
      @foreach ([
          ['grade' => 'Grade 7', 'students' => 168],
          ['grade' => 'Grade 8', 'students' => 167],
          ['grade' => 'Grade 9', 'students' => 166],
          ['grade' => 'Grade 10', 'students' => 167],
        ] as $grade)
        <a href="#grade-details" class="grade-card" data-grade="{{ $grade['grade'] }}"
          data-students="{{ $grade['students'] }}" aria-label="View {{ $grade['grade'] }} details">
          <div class="grade-card-top">
            <div class="grade-icon">
              <i class="fa-solid fa-address-book"></i>
            </div>

            <span class="grade-status active">
              <span class="status-dot"></span>
              Active
            </span>
          </div>

          <div class="grade-info">
            <span class="grade-eyebrow">JUNIOR HIGH SCHOOL</span>
            <h2>{{ $grade['grade'] }}</h2>
            <p>Grade-level information and academic records</p>
          </div>

          <div class="grade-card-bottom">
            <div class="grade-student-info">
              <span class="student-icon">
                <i class="fa-solid fa-users"></i>
              </span>

              <div>
                <strong>{{ $grade['students'] }}</strong>
                <span>Students</span>
              </div>
            </div>

            <span class="grade-view-link">
              View details
              <i class="fa-solid fa-arrow-right"></i>
            </span>
          </div>
        </a>
      @endforeach
    </div>
  </div>

  {{-- GRADE LEVEL DETAILS --}}
  <div id="gradeDetailsView" hidden>

    {{-- DETAILS HEADER --}}
    <div class="details-page-header">
      <button type="button" class="back-to-grades" id="backToGrades">
        <i class="fa-solid fa-arrow-left"></i>
        Back to Grade Levels
      </button>

      <div class="details-heading">
        <div class="details-grade-icon">
          <i class="fa-solid fa-graduation-cap"></i>
        </div>

        <div class="details-heading-info">
          <span class="grade-eyebrow">JUNIOR HIGH SCHOOL</span>
          <h2 id="detailsGradeTitle">Grade 7</h2>
          <p>Grade-level overview and academic information</p>
        </div>

        <span class="grade-status active details-status">
          <span class="status-dot"></span>
          Active
        </span>
      </div>
    </div>

    {{-- DETAILS SUMMARY --}}
    <div class="details-summary-grid">
      <div class="details-summary-card">
        <div class="details-summary-icon">
          <i class="fa-solid fa-users"></i>
        </div>
        <div>
          <span>Total Learners</span>
          <h3 id="detailsStudentCount">168</h3>
        </div>
      </div>

      <div class="details-summary-card">
        <div class="details-summary-icon">
          <i class="fa-solid fa-user-tie"></i>
        </div>
        <div>
          <span>Assigned Adviser</span>
          <h3 class="summary-placeholder">Not assigned</h3>
        </div>
      </div>

      <div class="details-summary-card">
        <div class="details-summary-icon">
          <i class="fa-solid fa-calendar-days"></i>
        </div>
        <div>
          <span>School Year</span>
          <h3>2026-2027</h3>
        </div>
      </div>
    </div>

    {{-- ASSIGNED ADVISER --}}
    <section class="details-panel">
      <div class="panel-heading">
        <div class="panel-heading-icon">
          <i class="fa-solid fa-user-tie"></i>
        </div>

        <div>
          <h3>Assigned Adviser</h3>
          <p>Teacher assigned to this grade level.</p>
        </div>
      </div>

      <div class="empty-state compact-empty-state">
        <span class="empty-state-icon">
          <i class="fa-regular fa-user"></i>
        </span>
        <h4>No Adviser Information Yet</h4>
        <p>
          The assigned adviser's information will appear here once
          connected to the system records.
        </p>
      </div>
    </section>

    {{-- SUBJECT ASSIGNMENTS --}}
    <section class="details-panel">
      <div class="panel-heading">
        <div class="panel-heading-icon">
          <i class="fa-solid fa-book-open"></i>
        </div>

        <div>
          <h3>Subject Assignments</h3>
          <p>Subjects and teachers assigned to this grade level.</p>
        </div>
      </div>

      <div class="empty-state compact-empty-state">
        <span class="empty-state-icon">
          <i class="fa-regular fa-folder-open"></i>
        </span>
        <h4>No Subject Assignments Yet</h4>
        <p>
          Subject and teacher assignments will appear here once
          the records are available.
        </p>
      </div>
    </section>

    {{-- LEARNERS' ACADEMIC RECORDS --}}
    <section class="details-panel academic-records-panel">
      <div class="panel-heading">
        <div class="panel-heading-icon">
          <i class="fa-solid fa-file-lines"></i>
        </div>

        <div>
          <h3>Learners' Academic Records</h3>
          <p>School Year 2026-2027</p>
          <p id="academicRecordsGradeLabel">Grade 7</p>
        </div>
      </div>

      {{-- SINGLE TERM DROPDOWN --}}
      <div class="academic-records-toolbar">
        <div class="records-filter-group">
          <label for="byTermFilter">By Term</label>

          <select id="byTermFilter" class="records-filter-select">
            <option value="overall">Overall Term</option>
            <option value="term1">Term 1</option>
            <option value="term2">Term 2</option>
            <option value="term3">Term 3</option>
          </select>
        </div>
      </div>

      {{-- ACADEMIC RECORDS TABLE --}}
      <div class="records-table-wrapper">
        <table class="records-table academic-records-table">
          <thead>
            <tr>
              <th>Learners</th>
              <th data-term-column="term1">Term 1</th>
              <th data-term-column="term2">Term 2</th>
              <th data-term-column="term3">Term 3</th>
              <th>Final Grade</th>
              <th>Descriptor</th>
              <th>Remark</th>
            </tr>
          </thead>

          <tbody id="academicRecordsBody">
            {{-- EMPTY STATE: no academic records yet --}}
            <tr>
              <td colspan="7" class="academic-records-empty">
                <div class="table-empty-state">
                  <span class="empty-state-icon">
                    <i class="fa-regular fa-folder-open"></i>
                  </span>

                  <h4>No Academic Records Yet</h4>
                  <p>
                    Learners' grades will appear here once
                    academic records are available for this
                    grade level and school year.
                  </p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
@endsection

@section('page-js')
  <script src="{{ asset('js/admin/grade-level-admin.js') }}"></script>
@endsection