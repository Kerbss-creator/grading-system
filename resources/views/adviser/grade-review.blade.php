{{-- EXTENDS ADVISER LAYOUT --}}
@extends('layouts.adviser')

@section('title', 'Grade Review')
@section('header', 'Grade Review')
@section('header-description', 'Review and monitor grade submissions from teachers.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/adviser/adviser-grade-review.css') }}">
@endsection

@section('content')


    <div class="grade-review-page">

        {{-- SUMMARY CARDS --}}
        <div class="review-summary">

            <div class="summary-card">
                <div class="summary-icon pending-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="summary-info">
                    <p>Pending Reviews</p>
                    <h3 id="pendingCount">{{ $pendingCount }}</h3>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon approved-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="summary-info">
                    <p>Forwarded to Admin</p>
                    <h3 id="forwardedCount">{{ $forwardedCount }}</h3>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon returned-icon">
                    <i class="fa-solid fa-rotate-left"></i>
                </div>
                <div class="summary-info">
                    <p>Returned Submissions</p>
                    <h3 id="returnedCount">{{ $returnedCount }}</h3>
                </div>
            </div>

        </div>

        {{-- REVIEW CONTENT --}}
        <div class="review-container">

            {{-- PAGE TITLE AND ASSIGNED GRADE LEVEL --}}
            <div class="review-header">
                <div>
                    <h2>Grade Submissions</h2>
                    <p>Review submitted grades before forwarding them to the administrator.</p>
                </div>


                {{-- ASSIGNED GRADE LEVEL --}}
                <div class="assigned-grade-box">

                    <div class="assigned-grade-icon">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>

                    <div class="assigned-grade-info">

                        <span class="assigned-label">YOUR ADVISORY ASSIGNMENT</span>

                        <strong class="assigned-grade-value">
                            @forelse($assignedGradeLevels as $gradeLevel)
                                <span class="grade-level-tag">
                                    {{ $gradeLevel }}
                                </span>
                            @empty
                                <span class="no-assignment">
                                    No Assigned Grade Level
                                </span>
                            @endforelse
                        </strong>

                        <div class="assigned-school-year">
                            <i class="fa-regular fa-calendar"></i>
                            <span>School Year</span>
                            <b>{{ $schoolYear }}</b>
                        </div>

                    </div>

                </div>

            </div>

            {{-- TABS --}}
            <div class="review-tabs">

                <button type="button" class="review-tab active" data-tab="pending">

                    <i class="fa-solid fa-inbox"></i>
                    Pending Reviews

                    <span class="tab-count">{{ $pendingCount }}</span>
                </button>

                <button type="button" class="review-tab" data-tab="history">

                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Review History
                </button>

            </div>

            {{-- FILTERS --}}
            <div class="review-filters">

                <div class="review-search">
                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input type="text" id="reviewSearch" placeholder="Search subject or teacher..."
                        aria-label="Search subject or teacher">
                </div>

                <div class="filter-group">
                    <label for="termFilter">Academic Term</label>

                    <select id="termFilter">
                        <option value="all">All Terms</option>
                        <option value="1st Term">1st Term</option>
                        <option value="2nd Term">2nd Term</option>
                        <option value="3rd Term">3rd Term</option>
                    </select>
                </div>

            </div>

            {{-- PENDING REVIEWS TABLE --}}
            <div class="review-table-wrapper" id="pendingPanel">

                <div class="table-heading">
                    <h3>Pending Reviews</h3>
                    <span>Submissions waiting for your review</span>
                </div>

                <table class="review-table">

                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th>Grade Level</th>
                            <th>Term</th>
                            <th>Date Submitted</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody id="pendingTableBody">

                        @forelse($pendingReviews as $submission)

                                            @php
                                                $subject = $submission->teachingAssignment?->subject ?? 'Unknown Subject';

                                                $faculty = $submission->faculty;

                                                $teacherName = $faculty
                                                    ? trim(
                                                        $faculty->first_name . ' ' .
                                                        ($faculty->middle_name ? $faculty->middle_name . ' ' : '') .
                                                        $faculty->last_name
                                                    )
                                                    : 'Unknown Teacher';

                                                $gradeLevel = $submission->grade_level ?? '—';

                                                $termLabel = match ((int) $submission->term) {
                                                    1 => '1st Term',
                                                    2 => '2nd Term',
                                                    3 => '3rd Term',
                                                    default => 'Unknown Term',
                                                };

                                                $submittedDate = $submission->submitted_at
                                                    ? \Carbon\Carbon::parse($submission->submitted_at)->format('M d, Y')
                                                    : '—';
                                            @endphp

                                            <tr data-search="{{ strtolower($subject . ' ' . $teacherName . ' ' . $gradeLevel) }}"
                                                data-term="{{ $termLabel }}">

                                                <td>
                                                    <div class="subject-cell">

                                                        <div class="subject-icon">
                                                            <i class="fa-solid fa-book-open"></i>
                                                        </div>

                                                        <div>
                                                            <strong>{{ $subject }}</strong>
                                                            <small>{{ $gradeLevel }}</small>
                                                        </div>

                                                    </div>
                                                </td>

                                                <td>{{ $teacherName }}</td>

                                                <td>{{ $gradeLevel }}</td>

                                                <td>{{ $termLabel }}</td>

                                                <td>{{ $submittedDate }}</td>

                                                <td>
                                                    <span class="status-badge status-pending">
                                                        Pending
                                                    </span>
                                                </td>

                                                <td>
                                                    <a href="{{ route('adviser.grade-review.show', [
                                'assignmentId' => $submission->teaching_assignment_id,
                                'schoolYear' => $submission->school_year,
                                'term' => $submission->term,
                            ]) }}" class="view-review-btn">
                                                        <i class="fas fa-eye"></i>
                                                        Review
                                                    </a>
                                                </td>

                                            </tr>

                        @empty

                            <tr class="empty-row">
                                <td colspan="7">
                                    No pending grade submissions found.
                                </td>
                            </tr>

                        @endforelse

                        <tr class="empty-row" hidden>
                            <td colspan="7">
                                No matching submissions found.
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

            {{-- REVIEW HISTORY TABLE --}}
            <div class="review-table-wrapper" id="historyPanel" hidden>

                <div class="table-heading">
                    <h3>Review History</h3>
                    <span>Previously reviewed grade submissions</span>
                </div>

                <table class="review-table">

                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th>Grade Level</th>
                            <th>Term</th>
                            <th>Date Submitted</th>
                            <th>Review Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody id="historyTableBody">

                        @forelse($reviewHistory as $submission)

                            @php
                                $subject = $submission->teachingAssignment?->subject ?? 'Unknown Subject';

                                $faculty = $submission->faculty;

                                $teacherName = $faculty
                                    ? trim(
                                        $faculty->first_name . ' ' .
                                        ($faculty->middle_name ? $faculty->middle_name . ' ' : '') .
                                        $faculty->last_name
                                    )
                                    : 'Unknown Teacher';

                                $gradeLevel = $submission->grade_level ?? '—';

                                $termLabel = match ((int) $submission->term) {
                                    1 => '1st Term',
                                    2 => '2nd Term',
                                    3 => '3rd Term',
                                    default => 'Unknown Term',
                                };

                                $submittedDate = $submission->submitted_at
                                    ? \Carbon\Carbon::parse($submission->submitted_at)->format('M d, Y')
                                    : '—';

                                $statusLabel = match ($submission->status) {
                                    'forwarded_to_admin' => 'Forwarded',
                                    'approved' => 'Approved',
                                    'returned' => 'Returned',
                                    default => ucfirst($submission->status),
                                };

                                $statusClass = match ($submission->status) {
                                    'forwarded_to_admin', 'approved' => 'status-approved',
                                    'returned' => 'status-returned',
                                    default => 'status-pending',
                                };
                            @endphp

                            <tr data-search="{{ strtolower($subject . ' ' . $teacherName . ' ' . $gradeLevel) }}"
                                data-term="{{ $termLabel }}">

                                <td>
                                    <div class="subject-cell">

                                        <div class="subject-icon">
                                            <i class="fa-solid fa-book-open"></i>
                                        </div>

                                        <div>
                                            <strong>{{ $subject }}</strong>
                                            <small>{{ $gradeLevel }}</small>
                                        </div>

                                    </div>
                                </td>

                                <td>{{ $teacherName }}</td>

                                <td>{{ $gradeLevel }}</td>

                                <td>{{ $termLabel }}</td>

                                <td>{{ $submittedDate }}</td>

                                <td>
                                    <span class="status-badge {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td>
                                    <button type="button" class="view-review-btn" data-subject="{{ $subject }}"
                                        data-teacher="{{ $teacherName }}" data-section="{{ $gradeLevel }}"
                                        data-term="{{ $termLabel }}" data-date="{{ $submittedDate }}"
                                        data-status="{{ $statusLabel }}">

                                        <i class="fa-regular fa-eye"></i>
                                        View

                                    </button>
                                </td>

                            </tr>

                        @empty

                            <tr class="empty-row">
                                <td colspan="7">
                                    No review history found.
                                </td>
                            </tr>

                        @endforelse

                        <tr class="empty-row" hidden>
                            <td colspan="7">
                                No matching submissions found.
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    {{-- REVIEW DETAILS MODAL --}}
    <div class="review-modal" id="reviewModal" aria-hidden="true">

        <div class="review-modal-backdrop" data-close-modal></div>

        <div class="review-modal-content" role="dialog" aria-modal="true" aria-labelledby="modalTitle">

            <div class="modal-header">

                <div>
                    <h3 id="modalTitle">Submission Details</h3>
                    <p>Review the information below.</p>
                </div>

                <button type="button" class="modal-close" data-close-modal aria-label="Close modal">

                    <i class="fa-solid fa-xmark"></i>

                </button>

            </div>

            <div class="modal-body">

                <div class="detail-item">
                    <span>Subject</span>
                    <strong id="modalSubject">—</strong>
                </div>

                <div class="detail-item">
                    <span>Teacher</span>
                    <strong id="modalTeacher">—</strong>
                </div>

                <div class="detail-item">
                    <span>Assigned Grade Level</span>
                    <strong id="modalSection">—</strong>
                </div>

                <div class="detail-item">
                    <span>Academic Term</span>
                    <strong id="modalTerm">—</strong>
                </div>

                <div class="detail-item">
                    <span>Date Submitted</span>
                    <strong id="modalDate">—</strong>
                </div>

                <div class="detail-item">
                    <span>Status</span>
                    <strong id="modalStatus">—</strong>
                </div>

            </div>

            <div class="modal-footer">

                <button type="button" class="modal-done-btn" data-close-modal>
                    Close
                </button>

            </div>

        </div>

    </div>

@endsection

@section('page-js')
    <script src="{{ asset('js/adviser/adviser-grade-review.js') }}"></script>
@endsection