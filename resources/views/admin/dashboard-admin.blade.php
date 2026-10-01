@extends('layouts.admin')

@section('title', 'Forbes Academy Dashboard')

@section('header', 'Dashboard')

@section('header-description', 'Welcome, Administrator!')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
@endsection

@section('content')

  <section class="dashboard-container">

    <!-- WELCOME CARD -->
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
              <small>School Year</small>
              <strong>2026 - 2027</strong>
            </div>
          </div>

          <div class="welcome-divider"></div>

          <div class="welcome-detail">
            <i class="fa-solid fa-book-open"></i>

            <div>
              <small>Term</small>
              <strong>1st Term</strong>
            </div>
          </div>

        </div>

      </div>

      <div class="welcome-icon">
        <i class="fa-solid fa-graduation-cap"></i>
      </div>

    </div>


    <!-- OVERVIEW -->
    <div class="section-header">

      <h2>Overview</h2>

      <span>Academic Year 2026-2027</span>

    </div>


    <!-- STATISTICS -->
    <div class="stats-grid">

      <!-- STUDENTS -->
      <div class="stat-card">

        <div class="stat-icon student">
          <i class="fa-solid fa-user-graduate"></i>
        </div>

        <div class="stat-info">

          <span>Total Students</span>

          <h3>668</h3>

          <small>
            <i class="fa-solid fa-users"></i>
            Grades 7 - 10
          </small>

        </div>

      </div>


      <!-- TEACHERS -->
      <div class="stat-card">

        <div class="stat-icon teacher">
          <i class="fa-solid fa-chalkboard-user"></i>
        </div>

        <div class="stat-info">

          <span>Total Teachers</span>

          <h3>42</h3>

          <small>
            <i class="fa-solid fa-user-check"></i>
            Registered teachers
          </small>

        </div>

      </div>


      <!-- CLASSES -->
      <div class="stat-card">

        <div class="stat-icon class">
          <i class="fa-solid fa-school"></i>
        </div>

        <div class="stat-info">

          <span>Total Classes</span>

          <h3>24</h3>

          <small>
            <i class="fa-solid fa-layer-group"></i>
            Grade 7 - 10
          </small>

        </div>

      </div>


      <!-- SUBJECTS -->
      <div class="stat-card">

        <div class="stat-icon subject">
          <i class="fa-solid fa-book"></i>
        </div>

        <div class="stat-info">

          <span>Total Subjects</span>

          <h3>12</h3>

          <small>
            <i class="fa-solid fa-book-open"></i>
            Registered subjects
          </small>

        </div>

      </div>

    </div>


    <!-- MAIN DASHBOARD CONTENT -->
    <div class="content-grid">


      <!-- STUDENTS BY GRADE -->
      <div class="content-card">

        <div class="card-header">

          <div>

            <h2>Students by Grade Level</h2>

            <p>
              Student distribution for Academic Year 2026-2027
            </p>

          </div>

        </div>


        <div class="grade-list">

          <!-- GRADE 7 -->
          <div class="grade-item">

            <div class="grade-info">
              <span>Grade 7</span>
              <strong>168 students</strong>
            </div>

            <div class="grade-bar">
              <div class="grade-progress" style="width: 100%;"></div>
            </div>

          </div>


          <!-- GRADE 8 -->
          <div class="grade-item">

            <div class="grade-info">
              <span>Grade 8</span>
              <strong>167 students</strong>
            </div>

            <div class="grade-bar">
              <div class="grade-progress" style="width: 99%;"></div>
            </div>

          </div>


          <!-- GRADE 9 -->
          <div class="grade-item">

            <div class="grade-info">
              <span>Grade 9</span>
              <strong>167 students</strong>
            </div>

            <div class="grade-bar">
              <div class="grade-progress" style="width: 99%;"></div>
            </div>

          </div>


          <!-- GRADE 10 -->
          <div class="grade-item">

            <div class="grade-info">
              <span>Grade 10</span>
              <strong>166 students</strong>
            </div>

            <div class="grade-bar">
              <div class="grade-progress" style="width: 98%;"></div>
            </div>

          </div>

        </div>

      </div>


      <!-- GRADE APPROVAL -->
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

              <strong>12</strong>

            </div>

          </div>


          <div class="approval-item">

            <div class="approval-icon approved">
              <i class="fa-solid fa-check"></i>
            </div>

            <div class="approval-info">

              <span>Approved</span>

              <strong>85</strong>

            </div>

          </div>


          <div class="approval-item">

            <div class="approval-icon returned">
              <i class="fa-solid fa-rotate-left"></i>
            </div>

            <div class="approval-info">

              <span>Returned</span>

              <strong>3</strong>

            </div>

          </div>

        </div>

      </div>

      <!-- RECENT USER ACTIVITIES -->
      <div class="content-card">

        <div class="card-header">

          <div>

            <h2>Recent User Activities</h2>

            <p>Latest activities in the system</p>

          </div>

        </div>


        <div class="activity-list">

          <div class="activity-item">

            <div class="activity-icon">
              <i class="fa-solid fa-user-plus"></i>
            </div>

            <div class="activity-info">

              <h3>New student added</h3>

              <p>Juan Dela Cruz was added to Grade 7-A.</p>

              <span>5 minutes ago</span>

            </div>

          </div>


          <div class="activity-item">

            <div class="activity-icon">
              <i class="fa-solid fa-file-circle-check"></i>
            </div>

            <div class="activity-info">

              <h3>Grades submitted</h3>

              <p>Teacher Maria Santos submitted Grade 8 grades.</p>

              <span>20 minutes ago</span>

            </div>

          </div>


          <div class="activity-item">

            <div class="activity-icon">
              <i class="fa-solid fa-user-pen"></i>
            </div>

            <div class="activity-info">

              <h3>Teacher account updated</h3>

              <p>Teacher information was successfully updated.</p>

              <span>1 hour ago</span>

            </div>

          </div>


          <div class="activity-item">

            <div class="activity-icon">
              <i class="fa-solid fa-check-circle"></i>
            </div>

            <div class="activity-info">

              <h3>Grades approved</h3>

              <p>Grade 9 Mathematics grades were approved.</p>

              <span>2 hours ago</span>

            </div>

          </div>

        </div>

      </div>


      <!-- ANNOUNCEMENTS -->
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




    <!-- ANNOUNCEMENT MODAL -->
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
  <script src="{{ asset('js/dashboards.js') }}"></script>
@endsection