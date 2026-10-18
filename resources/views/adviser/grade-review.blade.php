{{-- EXTENDS ADVISER LAYOUT --}}
@extends('layouts.adviser')

{{-- PAGE TITLE --}}
@section('title', 'Grade Review')

{{-- PAGE HEADER --}}
@section('header', 'Grade Review')

{{-- PAGE HEADER DESCRIPTION --}}
@section('header-description', 'Review and monitor grade submissions from teachers.')

{{-- PAGE CSS --}}
@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/adviser/adviser-grade-review.css') }}">
@endsection

{{-- MAIN CONTENT --}}
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
                    <h3 id="pendingCount">2</h3>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon approved-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="summary-info">
                    <p>Approved Reviews</p>
                    <h3 id="approvedCount">1</h3>
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-icon returned-icon">
                    <i class="fa-solid fa-rotate-left"></i>
                </div>
                <div class="summary-info">
                    <p>Returned Submissions</p>
                    <h3 id="returnedCount">1</h3>
                </div>
            </div>

        </div>

        {{-- REVIEW CONTENT --}}
        <div class="review-container">

            {{-- PAGE TITLE AND ASSIGNED SECTION --}}
            <div class="review-header">
                <div>
                    <h2>Grade Submissions</h2>
                    <p>Review submitted grades before forwarding them to the administrator.</p>
                </div>

                <div class="assigned-grade-box">
                    <div class="assigned-grade-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>

                    <div class="assigned-grade-info">
                        <span>Assigned Grade Level</span>
                        <strong>Grade 7-A</strong>
                        <small>School Year 2026–2027</small>
                    </div>
                </div>
            </div>

            {{-- TABS --}}
            <div class="review-tabs">
                <button type="button" class="review-tab active" data-tab="pending">
                    <i class="fa-solid fa-inbox"></i>
                    Pending Reviews
                    <span class="tab-count">2</span>
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
                            <th>Section</th>
                            <th>Term</th>
                            <th>Date Submitted</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody id="pendingTableBody">

                        <tr data-search="mathematics maria santos" data-term="1st Term">
                            <td>
                                <div class="subject-cell">
                                    <div class="subject-icon">
                                        <i class="fa-solid fa-calculator"></i>
                                    </div>
                                    <div>
                                        <strong>Mathematics</strong>
                                        <small>Grade 7</small>
                                    </div>
                                </div>
                            </td>
                            <td>Maria Santos</td>
                            <td>Grade 7-A</td>
                            <td>1st Term</td>
                            <td>Oct 15, 2026</td>
                            <td><span class="status-badge status-pending">Pending</span></td>
                            <td>
                                <button type="button" class="view-review-btn" data-subject="Mathematics"
                                    data-teacher="Maria Santos" data-section="Grade 7-A" data-term="1st Term"
                                    data-date="Oct 15, 2026" data-status="Pending">
                                    <i class="fa-regular fa-eye"></i>
                                    Review
                                </button>
                            </td>
                        </tr>

                        <tr data-search="science juan dela cruz" data-term="1st Term">
                            <td>
                                <div class="subject-cell">
                                    <div class="subject-icon science-icon">
                                        <i class="fa-solid fa-flask"></i>
                                    </div>
                                    <div>
                                        <strong>Science</strong>
                                        <small>Grade 7</small>
                                    </div>
                                </div>
                            </td>
                            <td>Juan Dela Cruz</td>
                            <td>Grade 7-A</td>
                            <td>1st Term</td>
                            <td>Oct 16, 2026</td>
                            <td><span class="status-badge status-pending">Pending</span></td>
                            <td>
                                <button type="button" class="view-review-btn" data-subject="Science"
                                    data-teacher="Juan Dela Cruz" data-section="Grade 7-A" data-term="1st Term"
                                    data-date="Oct 16, 2026" data-status="Pending">
                                    <i class="fa-regular fa-eye"></i>
                                    Review
                                </button>
                            </td>
                        </tr>

                        <tr class="empty-row" hidden>
                            <td colspan="7">No matching submissions found.</td>
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
                            <th>Section</th>
                            <th>Term</th>
                            <th>Date Reviewed</th>
                            <th>Review Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody id="historyTableBody">

                        <tr data-search="english ana reyes" data-term="1st Term">
                            <td>
                                <div class="subject-cell">
                                    <div class="subject-icon english-icon">
                                        <i class="fa-solid fa-book-open"></i>
                                    </div>
                                    <div>
                                        <strong>English</strong>
                                        <small>Grade 7</small>
                                    </div>
                                </div>
                            </td>
                            <td>Ana Reyes</td>
                            <td>Grade 7-A</td>
                            <td>1st Term</td>
                            <td>Oct 12, 2026</td>
                            <td><span class="status-badge status-approved">Forwarded</span></td>
                            <td>
                                <button type="button" class="view-review-btn" data-subject="English"
                                    data-teacher="Ana Reyes" data-section="Grade 7-A" data-term="1st Term"
                                    data-date="Oct 12, 2026" data-status="Forwarded">
                                    <i class="fa-regular fa-eye"></i>
                                    View
                                </button>
                            </td>
                        </tr>

                        <tr data-search="filipino pedro garcia" data-term="1st Term">
                            <td>
                                <div class="subject-cell">
                                    <div class="subject-icon filipino-icon">
                                        <i class="fa-solid fa-book"></i>
                                    </div>
                                    <div>
                                        <strong>Filipino</strong>
                                        <small>Grade 7</small>
                                    </div>
                                </div>
                            </td>
                            <td>Pedro Garcia</td>
                            <td>Grade 7-A</td>
                            <td>1st Term</td>
                            <td>Oct 10, 2026</td>
                            <td><span class="status-badge status-returned">Returned</span></td>
                            <td>
                                <button type="button" class="view-review-btn" data-subject="Filipino"
                                    data-teacher="Pedro Garcia" data-section="Grade 7-A" data-term="1st Term"
                                    data-date="Oct 10, 2026" data-status="Returned">
                                    <i class="fa-regular fa-eye"></i>
                                    View
                                </button>
                            </td>
                        </tr>

                        <tr class="empty-row" hidden>
                            <td colspan="7">No matching submissions found.</td>
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
                    <span>Assigned Section</span>
                    <strong id="modalSection">—</strong>
                </div>

                <div class="detail-item">
                    <span>Academic Term</span>
                    <strong id="modalTerm">—</strong>
                </div>

                <div class="detail-item">
                    <span>Date</span>
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

{{-- PAGE JS --}}
@section('page-js')
    <script src="{{ asset('js/adviser/adviser-grade-review.js') }}"></script>
@endsection