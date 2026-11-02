@extends('layouts.admin')

@section('title', 'Forbes Academy Dashboard')

@section('header', 'Dashboard')

@section('header-description')
Welcome, {{ auth()->user()?->name ?? 'Administrator' }}!
@endsection

@section('page-css')
  <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
@endsection

@section('content')

  <section class="dashboard-container">

    <div class="welcome-card">

      <div class="welcome-text">

        <span>FORBES ACADEMY</span>

        <h2>Welcome to the Grading System!</h2>

        <p>
          Manage students, teachers, subjects, classes, and academic
          records in one place.
        </p>

        <div class="welcome-details">

          <div class="welcome-detail">
            <i class="fa-solid fa-calendar"></i>

            <div>
              <small>Records for</small>
              <strong>All School Years</strong>
            </div>
          </div>

          <div class="welcome-divider"></div>

          <div class="welcome-detail">
            <i class="fa-solid fa-book-open"></i>

            <div>
              <small>View</small>
              <strong>All saved records</strong>
            </div>
          </div>

        </div>

      </div>

      <div class="welcome-icon">
        <i class="fa-solid fa-graduation-cap"></i>
      </div>

    </div>

    <div class="section-header">

      <h2>Overview</h2>

      <span>All school years</span>

    </div>

    <div class="stats-grid">

      <a class="stat-card" href="{{ route('admin.students.index') }}" aria-label="Open Student Management">

        <div class="stat-icon student">
          <i class="fa-solid fa-user-graduate"></i>
        </div>

        <div class="stat-info">

          <span>Total Students</span>

          <h3>{{ number_format($totalStudents) }}</h3>

          <small>
            <i class="fa-solid fa-users"></i>
            {{ number_format($activeStudents) }} active · {{ number_format($inactiveStudents) }} inactive
          </small>

        </div>

      </a>

      <a class="stat-card" href="{{ route('admin.teachers.index') }}" aria-label="Open Teacher Management">

        <div class="stat-icon teacher">
          <i class="fa-solid fa-chalkboard-user"></i>
        </div>

        <div class="stat-info">

          <span>Total Teachers</span>

          <h3>{{ number_format($totalTeachers) }}</h3>

          <small>
            <i class="fa-solid fa-user-check"></i>
            {{ number_format($activeTeachers) }} active · {{ number_format($inactiveTeachers) }} inactive
          </small>

        </div>

      </a>

      <a class="stat-card" href="{{ route('admin.grade-level') }}" aria-label="Open Grade Level Management">

        <div class="stat-icon class">
          <i class="fa-solid fa-school"></i>
        </div>

        <div class="stat-info">

          <span>Total Classes</span>

          <h3>{{ number_format($totalClasses) }}</h3>

          <small>
            <i class="fa-solid fa-layer-group"></i>
            Recorded grade/section combinations
          </small>

        </div>

      </a>

      <a class="stat-card" href="{{ route('admin.subjects') }}" aria-label="Open Subject Management">

        <div class="stat-icon subject">
          <i class="fa-solid fa-book"></i>
        </div>

        <div class="stat-info">

          <span>Total Subjects</span>

          <h3>{{ number_format($totalSubjects) }}</h3>

          <small>
            <i class="fa-solid fa-book-open"></i>
            From teacher assignments
          </small>

        </div>

      </a>

    </div>

    <div class="dashboard-account-summary">
      <a href="{{ route('admin.admins.index') }}" class="dashboard-account-link">
        <i class="fa-solid fa-user-shield"></i>
        Administrators: {{ number_format($activeAdmins) }} active · {{ number_format($inactiveAdmins) }} inactive
        <i class="fa-solid fa-arrow-up-right-from-square"></i>
      </a>
    </div>

    <div class="content-grid">

      <div class="content-card">

        <div class="card-header">

          <div>

            <h2>Students by Grade Level</h2>

            <p>
              Student distribution across all school years
            </p>

          </div>

        </div>

        <div class="grade-list">
          @forelse($studentsByGrade as $grade)

          <a class="grade-item grade-item-link" href="{{ route('admin.students.index', ['grade' => $grade->grade_level]) }}" aria-label="View students in {{ $grade->grade_level }}">

            <div class="grade-info">
              <span>{{ $grade->grade_level }}</span>
              <strong>{{ $grade->count }} students</strong>
            </div>

            <div class="grade-bar">

              <div class="grade-progress" style="width: {{ $totalStudents > 0 ? round(($grade->count / $totalStudents) * 100) : 0 }}%;"></div>
            </div>

          </a>
          @empty

          <div class="grade-item">
            <div class="grade-info">
              <span>No students added yet</span>
              <strong>0 students</strong>
            </div>
            <div class="grade-bar">
              <div class="grade-progress" style="width: 0%;"></div>
            </div>
          </div>
          @endforelse
        </div>

      </div>

      <div class="content-card">

        <div class="card-header">

          <div>

            <h2>Grade Approval</h2>

            <p>Current grade submission status</p>

          </div>

          <a href="{{ url('/admin/grade-approval') }}" class="view-btn">
            View Details
          </a>

        </div>

        <div class="approval-list">

          <div class="approval-item">

            <div class="approval-icon pending">
              <i class="fa-solid fa-clock"></i>
            </div>

            <div class="approval-info">

              <span>Pending</span>

              <strong>{{ $pendingGrades }}</strong>

            </div>

          </div>

          <div class="approval-item">

            <div class="approval-icon approved">
              <i class="fa-solid fa-check"></i>
            </div>

            <div class="approval-info">

              <span>Approved</span>

              <strong>{{ $approvedGrades }}</strong>

            </div>

          </div>

          <div class="approval-item">

            <div class="approval-icon returned">
              <i class="fa-solid fa-rotate-left"></i>
            </div>

            <div class="approval-info">

              <span>Returned</span>

              <strong>{{ $returnedGrades }}</strong>

            </div>

          </div>

        </div>

      </div>

      <div class="content-card">

        <div class="card-header">

          <div>

            <h2>Recent User Activities</h2>

            <p>Latest activities in the system</p>

          </div>

        </div>

        <div class="activity-list">

          @forelse($recentStudents as $student)
          <div class="activity-item">

            <div class="activity-icon">
              <i class="fa-solid fa-user-plus"></i>
            </div>

            <div class="activity-info">

              <h3>New student added</h3>

              <p>{{ $student->first_name }} {{ $student->last_name }} was added to {{ $student->grade_level }}{{ $student->section ? '-' . $student->section : '' }}.</p>

              <span>{{ $student->created_at->diffForHumans() }}</span>

            </div>

          </div>
          @empty
          <div class="activity-item">
            <div class="activity-info">
              <p>No recent activities to display.</p>
            </div>
          </div>
          @endforelse

        </div>

      </div>

      <div class="content-card announcements">

        <div class="card-header">

          <div>

            <h2>Announcements</h2>

            <p>Latest school announcements</p>

          </div>

          <button class="view-btn" id="viewAllAnnouncementsBtn">
            View All
          </button>

        </div>

        <div class="announcement-list">

          <div class="announcement-item">

            <div class="announcement-icon">
              <i class="fa-solid fa-bullhorn"></i>
            </div>

            <div class="announcement-info">

              <h3>Grade Submission</h3>

              <p>
                Teachers are reminded to submit their final grades.
              </p>

              <span>Today</span>

            </div>

          </div>

          <div class="announcement-item">

            <div class="announcement-icon">
              <i class="fa-solid fa-calendar"></i>
            </div>

            <div class="announcement-info">

              <h3>Quarterly Evaluation</h3>

              <p>
                Quarterly evaluation will begin next week.
              </p>

              <span>Yesterday</span>

            </div>

          </div>

          <div class="announcement-item">

            <div class="announcement-icon">
              <i class="fa-solid fa-circle-info"></i>
            </div>

            <div class="announcement-info">

              <h3>System Update</h3>

              <p>
                The grading system has been updated.
              </p>

              <span>August 22, 2026</span>

            </div>

          </div>

        </div>

      </div>

    </div>

    <div class="modal-overlay" id="announcementModal">

      <div class="modal-box">

        <div class="modal-header">

          <div>

            <h2>Announcements</h2>

            <p>Full list of school announcements</p>

          </div>

          <button class="modal-close" id="closeAnnouncementModal">

            <i class="fa-solid fa-xmark"></i>

          </button>

        </div>

        <div class="modal-body">

          <div class="announcement-list">

            <div class="announcement-item">

              <div class="announcement-icon">
                <i class="fa-solid fa-bullhorn"></i>
              </div>

              <div class="announcement-info">

                <h3>Grade Submission</h3>

                <p>
                  Teachers are reminded to submit their final grades.
                </p>

                <span>Today</span>

              </div>

            </div>

            <div class="announcement-item">

              <div class="announcement-icon">
                <i class="fa-solid fa-calendar"></i>
              </div>

              <div class="announcement-info">

                <h3>Quarterly Evaluation</h3>

                <p>
                  Quarterly evaluation will begin next week.
                </p>

                <span>Yesterday</span>

              </div>

            </div>

            <div class="announcement-item">

              <div class="announcement-icon">
                <i class="fa-solid fa-circle-info"></i>
              </div>

              <div class="announcement-info">

                <h3>System Update</h3>

                <p>
                  The grading system has been updated.
                </p>

                <span>August 22, 2026</span>

              </div>

            </div>

          </div>

        </div>

      </div>

    </div>

  </section>

@endsection

@section('page-js')
  <script src="{{ asset('js/dashboards.js') }}">
</script>
@endsection
