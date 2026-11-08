@extends('layouts.admin')

@section('title', 'Grade Approval')
@section('header', 'Grade Approval')
@section('header-description', 'Review and approve submitted student grades.')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('css/admin/grade-approval-admin.css') }}">
@endsection

@section('content')

  <!-- PAGE ACTIONS -->
  <div class="page-header">
    <div class="header-actions"></div>
  </div>

  <!-- SUMMARY CARDS -->
  <div class="summary-grid">
    <div class="summary-card">
      <div class="summary-icon pending-icon">
        <i class="fa-solid fa-clock"></i>
      </div>
      <div class="summary-info">
        <span>Pending</span>
        <h2 id="pendingCount">0</h2>
      </div>
    </div>

    <div class="summary-card">
      <div class="summary-icon approved-icon">
        <i class="fa-solid fa-circle-check"></i>
      </div>
      <div class="summary-info">
        <span>Approved</span>
        <h2 id="approvedCount">0</h2>
      </div>
    </div>

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
      <p>Review submitted grades before approval.</p>
    </div>
  </div>

  <!-- GRADE SUBMISSION TABLE -->
  <div class="approval-table-container">
    <table class="approval-table" id="approvalGrid">
      <thead>
        <tr>
          <th>Grade Level</th>
          <th>Subject</th>
          <th>Teacher</th>
          <th>School Year</th>
          <th>Submitted</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>

      <tbody>
        @forelse($submissions as $submission)
            @php
              $statusLabel = match ($submission->status) {
                'forwarded_to_admin' => 'Pending',
                'approved' => 'Approved',
                'returned' => 'Returned',
                default => 'Unknown',
              };

              $statusClass = match ($submission->status) {
                'forwarded_to_admin' => 'pending',
                'approved' => 'approved',
                'returned' => 'returned',
                default => '',
              };
            @endphp

            <tr class="approval-row" data-status="{{ $statusLabel }}"
              data-assignment="{{ $submission->teaching_assignment_id }}" data-school-year="{{ $submission->school_year }}"
              data-term="{{ $submission->term }}" data-grade="{{ $submission->grade_level }}"
              data-subject="{{ $submission->subject }}" data-teacher="{{ $submission->teacher_name }}"
              data-students="{{ $submission->student_count }}"
              data-submitted="{{ $submission->submitted_at ? $submission->submitted_at->format('M d, Y h:i A') : 'N/A' }}"
              data-reason="{{ $submission->return_reason ?? '' }}">
              <td>
                <div class="grade-cell">
                  {{ $submission->grade_level }}
                </div>
              </td>

              <td>
                <div class="subject-cell">
                  {{ $submission->subject }}
                </div>
              </td>

              <td>{{ $submission->teacher_name }}</td>
              <td>{{ $submission->school_year }}</td>

              <td>
                {{ $submission->submitted_at
          ? $submission->submitted_at->format('M d, Y')
          : 'N/A' }}
              </td>

              <td>
                <span class="status {{ $statusClass }}">
                  {{ $statusLabel }}
                </span>
              </td>

              <td>
                <div class="table-actions">
                  <span class="view-details" onclick="openReview(this)" title="View Details">
                    View Details
                  </span>
                </div>
              </td>
            </tr>
        @empty
          <tr class="empty-state-row">
            <td colspan="7">
              <div class="empty-state">
                <div class="empty-state-icon">
                  <i class="fa-solid fa-file-circle-check"></i>
                </div>

                <h3>No Grade Submissions Yet</h3>

                <p>
                  There are currently no grade submissions waiting for review.
                  Submitted grades will appear here.
                </p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <!-- REVIEW GRADE MODAL -->
  <div class="modal" id="reviewModal">
    <div class="modal-box">
      <div class="modal-header">
        <div>
          <h2>Review Grade Submission</h2>
          <p>Review the submitted grade information.</p>
        </div>

        <button id="closeReviewModal" type="button">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="review-title">
        <div class="large-review-icon">
          <i class="fa-solid fa-file-lines"></i>
        </div>

        <div>
          <h2 id="reviewGrade">Grade Level</h2>
          <p id="reviewSubject">Subject</p>
        </div>
      </div>

      <div class="review-details">
        <div>
          <span>Teacher</span>
          <strong id="reviewTeacher">N/A</strong>
        </div>

        <div>
          <span>Students</span>
          <strong id="reviewStudents">0</strong>
        </div>

        <div>
          <span>School Year</span>
          <strong id="reviewSchoolYear">N/A</strong>
        </div>

        <div>
          <span>Submitted</span>
          <strong id="reviewDate">N/A</strong>
        </div>

        <div>
          <span>Status</span>
          <strong id="reviewStatus">Pending</strong>
        </div>
      </div>

      <div class="review-note">
        <i class="fa-solid fa-circle-info"></i>
        <p>Review the submitted grades carefully before approving.</p>
      </div>

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
      <div class="modal-header">
        <div>
          <h2>Approve Grade Submission</h2>
          <p>Confirm approval of the submitted grades.</p>
        </div>

        <button id="closeApproveModal" type="button">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="approve-content">
        <div class="approve-icon">
          <i class="fa-solid fa-circle-check"></i>
        </div>

        <h3>Approve these grades?</h3>

        <p>
          Are you sure you want to approve the grades for
          <strong id="approveGradeName">Grade Level</strong>?
        </p>

        <div class="approve-info">
          <div>
            <span>Subject</span>
            <strong id="approveSubject">N/A</strong>
          </div>

          <div>
            <span>Teacher</span>
            <strong id="approveTeacher">N/A</strong>
          </div>

          <div>
            <span>Students</span>
            <strong id="approveStudents">0</strong>
          </div>
        </div>

        <div class="approve-note">
          <i class="fa-solid fa-circle-info"></i>
          <p>
            Once approved, the grades will be marked as approved
            and will be available for viewing.
          </p>
        </div>
      </div>

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
      <div class="modal-header">
        <div>
          <h2>Return Grade Submission</h2>
          <p>Provide a reason for returning the grades.</p>
        </div>

        <button id="closeReturnModal" type="button">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <div class="return-warning">
        <i class="fa-solid fa-circle-exclamation"></i>
        <p>The teacher will need to review and resubmit the grades.</p>
      </div>

      <div class="form-group">
        <label for="returnReason">Reason</label>
        <textarea id="returnReason" name="reason" required
          placeholder="Enter reason for returning the grade submission..."></textarea>
      </div>

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

  <!-- ACTUAL POST FORMS -->
  <form id="approveForm" method="POST" action="" data-base-url="{{ url('/admin/grade-approval') }}"
    style="display: none;">
    @csrf
  </form>

  <form id="returnForm" method="POST" action="" data-base-url="{{ url('/admin/grade-approval') }}" style="display: none;">
    @csrf
    <input type="hidden" name="reason" id="returnReasonInput">
  </form>
@endsection

@section('page-js')
  <script src="{{ asset('js/admin/grade-approval-admin.js') }}"></script>
@endsection