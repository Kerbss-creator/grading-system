@extends('layouts.admin')

@section('title', 'Grade Approval')

@section('header', 'Grade Approval')

@section('header-description', 'Review and approve submitted student grades.')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('css/grade-approval-admin.css') }}">
@endsection

@section('content')

  <!-- PAGE ACTIONS -->
  <div class="page-header">

    <div class="header-actions">

      <!-- Add any page-specific buttons here if needed -->

    </div>

  </div>


  <!-- SUMMARY CARDS -->
  <div class="summary-grid">

    <!-- Pending -->
    <div class="summary-card">

      <div class="summary-icon pending-icon">
        <i class="fa-solid fa-clock"></i>
      </div>

      <div class="summary-info">
        <span>Pending</span>
        <h2 id="pendingCount">0</h2>
      </div>

    </div>


    <!-- Approved -->
    <div class="summary-card">

      <div class="summary-icon approved-icon">
        <i class="fa-solid fa-circle-check"></i>
      </div>

      <div class="summary-info">
        <span>Approved</span>
        <h2 id="approvedCount">0</h2>
      </div>

    </div>


    <!-- Returned -->
    <div class="summary-card">

      <div class="summary-icon returned-icon">
        <i class="fa-solid fa-rotate-left"></i>
      </div>

      <div class="summary-info">
        <span>Returned</span>
        <h2 id="returnedCount">0</h2>
      </div>

    </div>

  </div>


  <!-- SEARCH AND FILTER -->
  <div class="toolbar">

    <div class="search-box">

      <i class="fa-solid fa-search"></i>

      <input type="text" id="searchApproval" placeholder="Search grade submissions...">

    </div>


    <select id="statusFilter">

      <option value="all">All Status</option>
      <option value="Pending">Pending</option>
      <option value="Approved">Approved</option>
      <option value="Returned">Returned</option>

    </select>

  </div>


  <!-- GRADE SUBMISSION SECTION -->
  <div class="section-header">

    <div>

      <h2>Grade Submissions</h2>

      <p>
        Review submitted grades before approval.
      </p>

    </div>

  </div>


  <!-- GRADE SUBMISSION TABLE -->
  <div class="approval-table-container">

    <table class="approval-table" id="approvalGrid">

      <thead>

        <tr>

          <th>Grade & Section</th>
          <th>Subject</th>
          <th>Teacher</th>
          <th>School Year</th>
          <th>Submitted</th>
          <th>Status</th>
          <th>Action</th>

        </tr>

      </thead>


      <tbody>

        <!-- GRADE 7 -->
        <tr class="approval-row" data-id="1" data-status="Pending" data-grade="Grade 7 - Rizal" data-subject="Mathematics"
          data-teacher="Juan Dela Cruz" data-school-year="2026-2027" data-date="Aug 26, 2026">

          <td>

            <div class="grade-cell">

              <div class="table-icon">

                <i class="fa-solid fa-file-lines"></i>

              </div>

              <div>

                <strong>Grade 7 - Rizal</strong>
                <span>Grade 7</span>

              </div>

            </div>

          </td>


          <td>

            <span class="subject-cell">

              <i class="fa-solid fa-book"></i>

              Mathematics

            </span>

          </td>


          <td>
            Juan Dela Cruz
          </td>


          <td>
            2026-2027
          </td>


          <td>
            Aug 26, 2026
          </td>


          <td>

            <span class="status pending">
              Pending
            </span>

          </td>


          <td>

            <div class="table-actions">

              <button class="review-btn" title="Review" type="button">
                <i class="fa-solid fa-eye"></i>
              </button>


              <button class="approve-btn" title="Approve" type="button">
                <i class="fa-solid fa-check"></i>
              </button>


              <button class="return-btn" title="Return" type="button">
                <i class="fa-solid fa-rotate-left"></i>
              </button>

            </div>

          </td>

        </tr>


        <!-- GRADE 8 -->
        <tr class="approval-row" data-id="2" data-status="Pending" data-grade="Grade 8 - Bonifacio" data-subject="Science"
          data-teacher="Maria Santos" data-school-year="2026-2027" data-date="Aug 25, 2026">

          <td>

            <div class="grade-cell">

              <div class="table-icon">

                <i class="fa-solid fa-file-lines"></i>

              </div>

              <div>

                <strong>Grade 8 - Bonifacio</strong>
                <span>Grade 8</span>

              </div>

            </div>

          </td>


          <td>

            <span class="subject-cell">

              <i class="fa-solid fa-book"></i>

              Science

            </span>

          </td>


          <td>
            Maria Santos
          </td>


          <td>
            2026-2027
          </td>


          <td>
            Aug 25, 2026
          </td>


          <td>

            <span class="status pending">
              Pending
            </span>

          </td>


          <td>

            <div class="table-actions">

              <button class="review-btn" title="Review" type="button">
                <i class="fa-solid fa-eye"></i>
              </button>


              <button class="approve-btn" title="Approve" type="button">
                <i class="fa-solid fa-check"></i>
              </button>


              <button class="return-btn" title="Return" type="button">
                <i class="fa-solid fa-rotate-left"></i>
              </button>

            </div>

          </td>

        </tr>


        <!-- GRADE 9 -->
        <tr class="approval-row" data-id="3" data-status="Approved" data-grade="Grade 9 - Mabini" data-subject="English"
          data-teacher="Pedro Reyes" data-school-year="2026-2027" data-date="Aug 24, 2026">

          <td>

            <div class="grade-cell">

              <div class="table-icon approved-table-icon">

                <i class="fa-solid fa-file-circle-check"></i>

              </div>

              <div>

                <strong>Grade 9 - Mabini</strong>
                <span>Grade 9</span>

              </div>

            </div>

          </td>


          <td>

            <span class="subject-cell">

              <i class="fa-solid fa-book"></i>

              English

            </span>

          </td>


          <td>
            Pedro Reyes
          </td>


          <td>
            2026-2027
          </td>


          <td>
            Aug 24, 2026
          </td>


          <td>

            <span class="status approved">
              Approved
            </span>

          </td>


          <td>

            <div class="table-actions">

              <button class="review-btn" title="View" type="button">
                <i class="fa-solid fa-eye"></i>
              </button>

            </div>

          </td>

        </tr>


        <!-- GRADE 10 -->
        <tr class="approval-row" data-id="4" data-status="Returned" data-grade="Grade 10 - Bonifacio"
          data-subject="Filipino" data-teacher="Ana Garcia" data-school-year="2026-2027" data-date="Aug 23, 2026">

          <td>

            <div class="grade-cell">

              <div class="table-icon returned-table-icon">

                <i class="fa-solid fa-file-circle-xmark"></i>

              </div>

              <div>

                <strong>Grade 10 - Bonifacio</strong>
                <span>Grade 10</span>

              </div>

            </div>

          </td>


          <td>

            <span class="subject-cell">

              <i class="fa-solid fa-book"></i>

              Filipino

            </span>

          </td>


          <td>
            Ana Garcia
          </td>


          <td>
            2026-2027
          </td>


          <td>
            Aug 23, 2026
          </td>


          <td>

            <span class="status returned">
              Returned
            </span>

          </td>


          <td>

            <div class="table-actions">

              <button class="review-btn" title="View" type="button">
                <i class="fa-solid fa-eye"></i>
              </button>

            </div>

          </td>

        </tr>

      </tbody>

    </table>

  </div>


  <!-- REVIEW GRADE MODAL -->
  <div class="modal" id="reviewModal">

    <div class="modal-box">

      <!-- Modal Header -->
      <div class="modal-header">

        <div>

          <h2>
            Review Grade Submission
          </h2>

          <p>
            Review the submitted grade information.
          </p>

        </div>


        <button id="closeReviewModal" type="button">

          <i class="fa-solid fa-xmark"></i>

        </button>

      </div>


      <!-- Grade Information -->
      <div class="review-title">

        <div class="large-review-icon">

          <i class="fa-solid fa-file-lines"></i>

        </div>


        <div>

          <h2 id="reviewGrade">
            Grade 7 - Rizal
          </h2>

          <p id="reviewSubject">
            Mathematics
          </p>

        </div>

      </div>


      <!-- Review Details -->
      <div class="review-details">

        <div>

          <span>Teacher</span>

          <strong id="reviewTeacher">
            Juan Dela Cruz
          </strong>

        </div>


        <div>

          <span>Students</span>

          <strong id="reviewStudents">
            42
          </strong>

        </div>


        <div>

          <span>School Year</span>

          <strong id="reviewSchoolYear">
            2026-2027
          </strong>

        </div>


        <div>

          <span>Submitted</span>

          <strong id="reviewDate">
            Aug 26, 2026
          </strong>

        </div>


        <div>

          <span>Status</span>

          <strong id="reviewStatus">
            Pending
          </strong>

        </div>

      </div>


      <!-- Review Information -->
      <div class="review-note">

        <i class="fa-solid fa-circle-info"></i>

        <p>
          Review the submitted grades carefully before approving.
        </p>

      </div>


      <!-- Modal Buttons -->
      <div class="modal-actions">

        <button class="cancel-btn" id="closeReviewButton" type="button">
          Close
        </button>


        <button class="return-modal-btn" id="reviewReturnBtn" type="button">

          <i class="fa-solid fa-rotate-left"></i>

          Return

        </button>


        <button class="approve-modal-btn" id="reviewApproveBtn" type="button">

          <i class="fa-solid fa-check"></i>

          Approve

        </button>

      </div>

    </div>

  </div>


  <!-- APPROVE GRADE MODAL -->
  <div class="modal" id="approveModal">

    <div class="modal-box approve-box">

      <!-- Modal Header -->
      <div class="modal-header">

        <div>

          <h2>
            Approve Grade Submission
          </h2>

          <p>
            Confirm approval of the submitted grades.
          </p>

        </div>


        <button id="closeApproveModal" type="button">

          <i class="fa-solid fa-xmark"></i>

        </button>

      </div>


      <!-- Approval Content -->
      <div class="approve-content">

        <div class="approve-icon">

          <i class="fa-solid fa-circle-check"></i>

        </div>


        <h3>
          Approve these grades?
        </h3>


        <p>

          Are you sure you want to approve the grades for

          <strong id="approveGradeName">
            Grade 7 - Rizal
          </strong>?

        </p>


        <!-- Approval Information -->
        <div class="approve-info">

          <div>

            <span>Subject</span>

            <strong id="approveSubject">
              Mathematics
            </strong>

          </div>


          <div>

            <span>Teacher</span>

            <strong id="approveTeacher">
              Juan Dela Cruz
            </strong>

          </div>


          <div>

            <span>Students</span>

            <strong id="approveStudents">
              42
            </strong>

          </div>

        </div>


        <!-- Approval Note -->
        <div class="approve-note">

          <i class="fa-solid fa-circle-info"></i>

          <p>

            Once approved, the grades will be marked as approved
            and will be available for viewing.

          </p>

        </div>

      </div>


      <!-- Approval Buttons -->
      <div class="modal-actions">

        <button class="cancel-btn" id="cancelApprove" type="button">
          Cancel
        </button>


        <button class="approve-modal-btn" id="confirmApprove" type="button">

          <i class="fa-solid fa-check"></i>

          Approve Grades

        </button>

      </div>

    </div>

  </div>


  <!-- RETURN GRADE MODAL -->
  <div class="modal" id="returnModal">

    <div class="modal-box">

      <!-- Modal Header -->
      <div class="modal-header">

        <div>

          <h2>
            Return Grade Submission
          </h2>

          <p>
            Provide a reason for returning the grades.
          </p>

        </div>


        <button id="closeReturnModal" type="button">

          <i class="fa-solid fa-xmark"></i>

        </button>

      </div>


      <!-- Warning Message -->
      <div class="return-warning">

        <i class="fa-solid fa-circle-exclamation"></i>

        <p>
          The teacher will need to review and resubmit the grades.
        </p>

      </div>


      <!-- Return Reason -->
      <div class="form-group">

        <label for="returnReason">
          Reason
        </label>

        <textarea id="returnReason" placeholder="Enter reason for returning the grade submission..."></textarea>

      </div>


      <!-- Modal Buttons -->
      <div class="modal-actions">

        <button class="cancel-btn" id="cancelReturn" type="button">
          Cancel
        </button>


        <button class="return-submit-btn" id="submitReturn" type="button">

          <i class="fa-solid fa-rotate-left"></i>

          Return Grades

        </button>

      </div>

    </div>

  </div>

@endsection


@section('page-js')

  <script src="{{ asset('js/grade-approval-admin.js') }}"></script>

@endsection