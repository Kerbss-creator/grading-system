@extends('layouts.admin')

@section('title', 'Forbes Academy Dashboard')

@section('header', 'Dashboard')

@section('header-description', 'Welcome back, Administrator!')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endsection

@section('content')

  <section class="dashboard-container">

    <!-- WELCOME CARD -->
    <div class="welcome-card">

      <div class="welcome-text">

        <span>FORBES ACADEMY</span>

        <h2>Welcome to the Grading System!</h2>

        <p>
          Manage students, teachers, subjects, classes, and academic records
          in one place.
        </p>

      </div>

      <div class="welcome-icon">
        <i class="fa-solid fa-graduation-cap"></i>
      </div>

    </div>


    <!-- STATISTICS -->
    <div class="section-header">

      <h2>Overview</h2>

      <span>Academic Year 2026-2027</span>

    </div>


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
            <i class="fa-solid fa-arrow-up"></i>
            Active students
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
            <i class="fa-solid fa-check"></i>
            Active teachers
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
            Grade 7-10
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


    <!-- LOWER CONTENT -->
    <div class="content-grid">


      <!-- RECENT GRADE ACTIVITY -->
      <div class="content-card">

        <div class="card-header">

          <div>

            <h2>Recent Grade Activity</h2>

            <p>Latest grade records submitted</p>

          </div>

          <button class="view-btn" id="viewAllGradesBtn">
            View All
          </button>

        </div>


        <div class="table-container">

          <table>

            <thead>

              <tr>
                <th>Student</th>
                <th>Subject</th>
                <th>Final Grade</th>
                <th>Status</th>
              </tr>

            </thead>


            <tbody>

              <tr>

                <td>

                  <div class="student-name">

                    <div class="student-avatar">
                      JD
                    </div>

                    Juan Dela Cruz

                  </div>

                </td>

                <td>Mathematics</td>

                <td>
                  <strong>92</strong>
                </td>

                <td>
                  <span class="status passed">
                    Passed
                  </span>
                </td>

              </tr>


              <tr>

                <td>

                  <div class="student-name">

                    <div class="student-avatar">
                      AS
                    </div>

                    Ana Santos

                  </div>

                </td>

                <td>Science</td>

                <td>
                  <strong>89</strong>
                </td>

                <td>
                  <span class="status passed">
                    Passed
                  </span>
                </td>

              </tr>


              <tr>

                <td>

                  <div class="student-name">

                    <div class="student-avatar">
                      MR
                    </div>

                    Mark Reyes

                  </div>

                </td>

                <td>English</td>

                <td>
                  <strong>84</strong>
                </td>

                <td>
                  <span class="status passed">
                    Passed
                  </span>
                </td>

              </tr>


              <tr>

                <td>

                  <div class="student-name">

                    <div class="student-avatar">
                      LC
                    </div>

                    Lisa Cruz

                  </div>

                </td>

                <td>Filipino</td>

                <td>
                  <strong>76</strong>
                </td>

                <td>
                  <span class="status passed">
                    Passed
                  </span>
                </td>

              </tr>

            </tbody>

          </table>

        </div>

      </div>


      <!-- GRADE ACTIVITY MODAL -->
      <div class="modal-overlay" id="gradeActivityModal">

        <div class="modal-box">

          <div class="modal-header">

            <div>
              <h2>Recent Grade Activity</h2>
              <p>Full list of latest grade records</p>
            </div>

            <button class="modal-close" id="closeGradeModal">
              <i class="fa-solid fa-xmark"></i>
            </button>

          </div>

          <div class="modal-body">

            <div class="table-container">

              <table>

                <thead>
                  <tr>
                    <th>Student</th>
                    <th>Subject</th>
                    <th>Final Grade</th>
                    <th>Status</th>
                  </tr>
                </thead>

                <tbody>

                  <tr>
                    <td>
                      <div class="student-name">
                        <div class="student-avatar">JD</div>
                        Juan Dela Cruz
                      </div>
                    </td>
                    <td>Mathematics</td>
                    <td><strong>92</strong></td>
                    <td><span class="status passed">Passed</span></td>
                  </tr>

                  <tr>
                    <td>
                      <div class="student-name">
                        <div class="student-avatar">AS</div>
                        Ana Santos
                      </div>
                    </td>
                    <td>Science</td>
                    <td><strong>89</strong></td>
                    <td><span class="status passed">Passed</span></td>
                  </tr>

                  <tr>
                    <td>
                      <div class="student-name">
                        <div class="student-avatar">MR</div>
                        Mark Reyes
                      </div>
                    </td>
                    <td>English</td>
                    <td><strong>84</strong></td>
                    <td><span class="status passed">Passed</span></td>
                  </tr>

                  <tr>
                    <td>
                      <div class="student-name">
                        <div class="student-avatar">LC</div>
                        Lisa Cruz
                      </div>
                    </td>
                    <td>Filipino</td>
                    <td><strong>76</strong></td>
                    <td><span class="status passed">Passed</span></td>
                  </tr>

                  {{-- Add more rows here, or loop over a $recentGrades collection from your controller --}}

                </tbody>

              </table>

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
                Teachers are reminded to submit final grades.
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
                  <p>Teachers are reminded to submit final grades.</p>
                  <span>Today</span>
                </div>
              </div>

              <div class="announcement-item">
                <div class="announcement-icon">
                  <i class="fa-solid fa-calendar"></i>
                </div>
                <div class="announcement-info">
                  <h3>Quarterly Evaluation</h3>
                  <p>Quarterly evaluation will begin next week.</p>
                  <span>Yesterday</span>
                </div>
              </div>

              <div class="announcement-item">
                <div class="announcement-icon">
                  <i class="fa-solid fa-circle-info"></i>
                </div>
                <div class="announcement-info">
                  <h3>System Update</h3>
                  <p>The grading system has been updated.</p>
                  <span>August 22, 2026</span>
                </div>
              </div>

              {{-- Add more items here, or loop over an $announcements collection from your controller --}}

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