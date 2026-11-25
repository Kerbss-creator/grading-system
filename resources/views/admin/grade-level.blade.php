@extends('layouts.admin')

@section('title', 'Grade Level Management')
@section('header', 'Grade Level Management')
@section('header-description', 'View assigned teachers, class advisers, subject assignments, and academic records.')
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
          <h2 class="school-year-value">
            {{ $schoolYear ?? 'Not Set' }}
          </h2>
          <small>Junior High School</small>
        </div>
      </div>
    </div>

    {{-- GRADE LEVEL HEADER --}}
    <div class="section-header">
      <div>
        <h2>Grade Levels</h2>
        <p>
          Select a grade level to view its assigned teachers, class adviser,
          subject assignments, and learners' academic records.
        </p>
      </div>

      <div class="grade-level-badge">
        <i class="fa-solid fa-school"></i>
        Junior High School
      </div>
    </div>

    {{-- GRADE LEVEL CARDS --}}

    <div class="grade-grid" id="gradeGrid">

      @foreach ($grades as $grade)

        <a href="#grade-details" class="grade-card" data-grade="{{ $grade['grade'] }}"
          data-students="{{ $grade['students'] }}" data-adviser="{{ $grade['adviser'] ?? '' }}"
          data-subjects="{{ $grade['subjects']->toJson() }}" data-school-year="{{ $schoolYear ?? '' }}"
          aria-label="View {{ $grade['grade'] }} details">

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

            <div class="grade-card-assignment-info">

              {{-- Assigned Teachers --}}
              <div class="grade-card-metric">
                <span class="student-icon">
                  <i class="fa-solid fa-chalkboard-user"></i>
                </span>

                <div>
                  <strong>{{ $grade['teachers'] }}</strong>
                  <span>Teachers</span>
                </div>
              </div>

              {{-- Assigned Adviser --}}
              <div class="grade-card-adviser">
                <span class="student-icon">
                  <i class="fa-solid fa-user-tie"></i>
                </span>

                <div>
                  <strong>Class Adviser</strong>
                  <span>{{ $grade['adviser'] }}</span>
                </div>
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
          <h3 id="detailsAdviserSummary">Not assigned</h3>
        </div>
      </div>

      <div class="details-summary-card">
        <div class="details-summary-icon">
          <i class="fa-solid fa-calendar-days"></i>
        </div>
        <div>
          <span>School Year</span>
          <h3 id="detailsSchoolYear">{{ $schoolYear ?? 'Not Set' }}</h3>
        </div>
      </div>
    </div>


    {{-- SUBJECT TEACHERS --}}
    <section class="details-panel">
      <div class="panel-heading">
        <div class="panel-heading-icon">
          <i class="fa-solid fa-book-open"></i>
        </div>

        <div>
          <h3>Subject Teachers</h3>
          <p>Subjects and teachers assigned to this grade level.</p>
        </div>
      </div>

      <div id="subjectAssignmentsList" class="subject-assignments-list">
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
      </div>
    </section>
  </div>
@endsection

@section('page-js')
  <script src="{{ asset('js/admin/grade-level-admin.js') }}"></script>
@endsection