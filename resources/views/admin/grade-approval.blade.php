@extends('layouts.admin')

@section('title', 'Grade Approval')
@section('header', 'Grade Approval')
@section('header-description', 'Review and approve submitted student grades.')

@section('page-css')
  <link rel="stylesheet" href="{{ asset('css/admin/grade-approval-admin.css') }}">
@endsection

@section('content')

  <!-- SUCCESS / ERROR MESSAGES -->
  @if(session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="alert alert-danger">
      {{ session('error') }}
    </div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger">
      {{ $errors->first() }}
    </div>
  @endif

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
        <h2 id="pendingCount">{{ $pendingCount }}</h2>
      </div>
    </div>

    <div class="summary-card">
      <div class="summary-icon approved-icon">
        <i class="fa-solid fa-circle-check"></i>
      </div>

      <div class="summary-info">
        <span>Approved</span>
        <h2 id="approvedCount">{{ $approvedCount }}</h2>
      </div>
    </div>

    <div class="summary-card">
      <div class="summary-icon returned-icon">
        <i class="fa-solid fa-rotate-left"></i>
      </div>

      <div class="summary-info">
        <span>Returned</span>
        <h2 id="returnedCount">{{ $returnedCount }}</h2>
      </div>
    </div>

  </div>

  <!-- SEARCH AND FILTER -->
  <div class="toolbar">

    <div class="search-box">
      <i class="fa-solid fa-search"></i>
      <input type="text" id="searchApproval" placeholder="Search grade submissions...">
    </div>

    <!-- SCHOOL YEAR FILTER -->
    <form method="GET" action="{{ route('admin.grade-approval') }}" id="yearFilterForm">
      <select name="school_year" id="schoolYearFilter" onchange="this.form.submit()">
        @forelse($schoolYears as $year)
          <option value="{{ $year }}" {{ $schoolYear === $year ? 'selected' : '' }}>
            {{ $year }}
            @if($activeSchoolYear && $activeSchoolYear->year_name === $year)
              (Active)
            @endif
          </option>
        @empty
          <option value="">No School Year Available</option>
        @endforelse
      </select>
    </form>

    <!-- STATUS FILTER -->
    <select id="statusFilter">
      <option value="all">All Status</option>
      <option value="Pending">Pending</option>
      <option value="Approved">Approved</option>
      <option value="Returned">Returned</option>
      <option value="Needs Review">Needs Review</option>
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
          <th>Term</th>
          <th>Submitted</th>
          <th>Approval Status</th>
          <th>Publication Status</th>
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
                'inconsistent' => 'Needs Review',
                default => 'Unknown',
              };

              $statusClass = match ($submission->status) {
                'forwarded_to_admin' => 'pending',
                'approved' => 'approved',
                'returned' => 'returned',
                'inconsistent' => 'inconsistent',
                default => '',
              };
              $publicationLabel = $submission->status === 'approved'
                ? ($submission->publication_status === 'published'
                  ? 'Published'
                  : 'Not Published')
                : 'Not Applicable';

              $publicationClass = $submission->status === 'approved'
                ? ($submission->publication_status === 'published'
                  ? 'published'
                  : 'not-published')
                : 'not-applicable';
            @endphp

            <tr class="approval-row" data-status="{{ $statusLabel }}"
              data-assignment="{{ $submission->teaching_assignment_id }}" data-school-year="{{ $submission->school_year }}"
              data-term="{{ $submission->term }}" data-grade="{{ $submission->grade_level }}"
              data-subject="{{ $submission->subject }}" data-teacher="{{ $submission->teacher_name }}"
              data-students="{{ $submission->student_count }}"
              data-submitted="{{ $submission->submitted_at ? \Illuminate\Support\Carbon::parse($submission->submitted_at)->format('M d, Y h:i A') : 'N/A' }}"
              data-reason="{{ $submission->return_reason ?? '' }}" data-publication-status="{{ $publicationLabel }}">

              <td>
                <div class="grade-cell">
                  {{ $submission->grade_level ?? 'N/A' }}
                </div>
              </td>

              <td>
                <div class="subject-cell">
                  {{ $submission->subject ?? 'N/A' }}
                </div>
              </td>

              <td>{{ $submission->teacher_name }}</td>

              <td>{{ $submission->school_year }}</td>

              <td>
                Term {{ $submission->term }}
              </td>

              <td>
                {{ $submission->submitted_at
          ? \Illuminate\Support\Carbon::parse($submission->submitted_at)->format('M d, Y')
          : 'N/A'
                                                                                        }}
              </td>

              <td>
                <span class="status {{ $statusClass }}">
                  {{ $statusLabel }}
                </span>
              </td>

              <td>
                <span class="publication-status {{ $publicationClass }}">
                  {{ $publicationLabel }}
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
            <td colspan="9">

              <div class="empty-state">

                <div class="empty-state-icon">
                  <i class="fa-solid fa-file-circle-check"></i>
                </div>

                <h3>No Grade Submissions Found</h3>

                <p>
                  There are currently no grade submissions
                  available for {{ $schoolYear ?? 'the selected school year' }}.
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

      <!-- MODAL HEADER -->
      <div class="modal-header">

        <div>
          <h2>Review Grade Submission</h2>
          <p>Review the submitted grade information.</p>
        </div>

        <button id="closeReviewModal" type="button">
          <i class="fa-solid fa-xmark"></i>
        </button>

      </div>

      <!-- REVIEW TITLE -->
      <div class="review-title">

        <div class="large-review-icon">
          <i class="fa-solid fa-file-lines"></i>
        </div>

        <div>
          <h2 id="reviewGrade">Grade Level</h2>
          <p id="reviewSubject">Subject</p>
        </div>

      </div>

      <!-- REVIEW DETAILS -->
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
          <span>Term</span>
          <strong id="reviewTerm">N/A</strong>
        </div>

        <div>
          <span>Submitted</span>
          <strong id="reviewDate">N/A</strong>
        </div>

        <div>
          <span>Approval Status</span>
          <strong id="reviewStatus">Pending</strong>
        </div>

        <div>
          <span>Publication Status</span>
          <strong id="reviewPublicationStatus">Not Applicable</strong>
        </div>

      </div>

      <!-- RETURN REASON -->
      <div class="review-return-reason" id="reviewReturnReasonBox" style="display: none;">

        <strong>
          <i class="fa-solid fa-circle-exclamation"></i>
          Admin Return Reason
        </strong>

        <p id="reviewReturnReason"></p>

      </div>

      <!-- REVIEW NOTE -->
      <div class="review-note">

        <i class="fa-solid fa-circle-info"></i>

        <p>
          Review the submitted grades carefully before approving.
        </p>

      </div>

      <!-- MODAL ACTIONS -->
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

          <div>
            <span>Term</span>
            <strong id="approveTerm">N/A</strong>
          </div>

        </div>

        <div class="approve-note">
          <i class="fa-solid fa-circle-info"></i>
          <p>
            Once approved, the grades will be marked as approved.
            Students will only be able to view these grades after
            the Administrator publishes them through Grade Publication.
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