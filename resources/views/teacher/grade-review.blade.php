@extends('layouts.teacher')

@section('title', 'Grade Review - Forbes Academy')

@section('header', 'Grade Review')

@section('header-description')
    Review individual student grades and assessment records.
@endsection

@section('page-css')
    <style>
        .review-page {
            width: 100%;
            min-width: 0;
            padding: 5px 0 30px;
        }

        /* BACK BUTTON */
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 10px 16px;
            margin-bottom: 18px;
            background: #fff;
            color: #036f35;
            border: 1px solid #dce5de;
            border-radius: 9px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: .2s ease;
        }

        .back-button:hover {
            background: #036f35;
            color: #fff;
            border-color: #036f35;
            transform: translateX(-3px);
        }

        /* HEADING */
        .review-heading {
            background: #fff;
            padding: 24px;
            border-radius: 14px;
            border-left: 5px solid #036f35;
            margin-bottom: 20px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .04);
        }

        .review-heading h2 {
            margin: 0 0 9px;
            color: #183b29;
            font-size: 20px;
            font-weight: 700;
        }

        .review-heading p {
            margin: 5px 0;
            color: #68756d;
            font-size: 12px;
        }

        .review-heading strong {
            color: #315b40;
        }

        /* SUMMARY CARDS */
        .review-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .review-stat {
            display: flex;
            align-items: center;
            gap: 15px;
            background: #fff;
            padding: 19px;
            border-radius: 13px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .04);
            min-width: 0;
        }

        .review-stat-icon {
            width: 43px;
            height: 43px;
            min-width: 43px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 11px;
            background: #e8f5ec;
            color: #036f35;
            font-size: 17px;
        }

        .review-stat-info span {
            display: block;
            color: #778078;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .review-stat-info strong {
            display: block;
            color: #183b29;
            font-size: 23px;
            font-weight: 700;
        }

        /* MAIN CONTENT CARD */
        .review-card {
            background: #fff;
            padding: 22px;
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .04);
        }

        .review-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 18px;
        }

        .review-card-header h3 {
            margin: 0;
            color: #183b29;
            font-size: 16px;
            font-weight: 700;
        }

        .student-count {
            color: #68756d;
            background: #f2f7f3;
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* TABLE */
        .review-table-wrapper {
            width: 100%;
            overflow-x: auto;
            border: 1px solid #edf0ed;
            border-radius: 10px;
        }

        .review-table {
            width: 100%;
            min-width: 850px;
            border-collapse: collapse;
        }

        .review-table th {
            background: #f2f7f3;
            color: #315b40;
            padding: 14px 15px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
            border-bottom: 1px solid #e2eae3;
        }

        .review-table td {
            padding: 15px;
            border-bottom: 1px solid #edf0ed;
            color: #46534a;
            font-size: 12px;
            vertical-align: middle;
        }

        .review-table tbody tr:last-child td {
            border-bottom: none;
        }

        .review-table tbody tr.student-row:hover {
            background: #f9fcfa;
        }

        .student-name {
            display: block;
            color: #243c2c;
            font-weight: 700;
            font-size: 12px;
            min-width: 150px;
        }

        .student-id {
            display: block;
            color: #879188;
            font-size: 11px;
            margin-top: 4px;
        }

        /* TERM CELL */
        .term-grade {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 7px;
        }

        .grade-value {
            color: #183b29;
            font-size: 16px;
            font-weight: 700;
        }

        .no-grade {
            color: #9aa39c;
            font-size: 13px;
        }

        /* DESCRIPTOR */
        .descriptor-badge {
            display: inline-block;
            padding: 4px 9px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
        }

        .descriptor-passing {
            background: #dcfce7;
            color: #15803d;
        }

        .descriptor-failing {
            background: #fee2e2;
            color: #dc2626;
        }

        /* STATUS */
        .status-pill {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: capitalize;
            background: #f0f2f0;
            color: #647067;
        }

        .status-pill.submitted,
        .status-pill.approved,
        .status-pill.forwarded-to-admin {
            background: #e5f5eb;
            color: #08743a;
        }

        .status-pill.returned,
        .status-pill.declined {
            background: #fff0e8;
            color: #b85a25;
        }

        .status-pill.draft {
            background: #fff6dc;
            color: #947019;
        }

        /* DETAILS BUTTON */
        .details-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #dce9df;
            background: #f5faf6;
            color: #036f35;
            padding: 7px 10px;
            border-radius: 7px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
            transition: .2s;
            white-space: nowrap;
        }

        .details-toggle:hover {
            background: #036f35;
            color: #fff;
            border-color: #036f35;
        }

        /* ASSESSMENT DETAILS */
        .details-row td {
            background: #f8fbf8;
            padding: 18px !important;
        }

        .details-container {
            border: 1px solid #e2eae3;
            background: #fff;
            border-radius: 10px;
            padding: 16px;
        }

        .details-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .details-heading strong {
            color: #315b40;
            font-size: 13px;
        }

        .assessment-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(115px, 1fr));
            gap: 10px;
        }

        .assessment-item {
            border: 1px solid #e2eae3;
            background: #fafffb;
            padding: 11px;
            border-radius: 8px;
        }

        .assessment-item strong {
            display: block;
            color: #315b40;
            font-size: 11px;
            margin-bottom: 6px;
        }

        .assessment-item span {
            font-size: 12px;
            color: #56635a;
            font-weight: 600;
        }

        .empty-assessments {
            color: #849087;
            font-size: 12px;
            margin: 0;
        }

        /* RETURN FEEDBACK */
        .return-feedback-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
            margin-bottom: 16px;
        }

        .return-feedback {
            padding: 13px 15px;
            border-radius: 9px;
            border-left: 4px solid;
        }

        .return-feedback.adviser-feedback {
            background: #fff8e9;
            border-color: #d99a24;
        }

        .return-feedback.admin-feedback {
            background: #fff0ed;
            border-color: #c44735;
        }

        .return-feedback strong {
            display: block;
            margin-bottom: 7px;
            font-size: 12px;
            color: #34483a;
        }

        .return-feedback p {
            margin: 0;
            color: #5e6a61;
            font-size: 12px;
            line-height: 1.6;
            white-space: pre-line;
        }

        /* EMPTY STATE */
        .empty-state {
            padding: 45px 15px;
            text-align: center;
            color: #849087;
            font-size: 12px;
        }

        .empty-state i {
            display: block;
            font-size: 30px;
            color: #b7c9bb;
            margin-bottom: 12px;
        }

        /* FOOTNOTE */
        .review-note {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            color: #7b867e;
            font-size: 11px;
            line-height: 1.6;
            margin-top: 16px;
        }

        .review-note i {
            color: #036f35;
            margin-top: 2px;
        }

        @media (max-width: 1100px) {
            .review-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 900px) {
            .review-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .review-page {
                padding: 5px 0 25px;
            }

            .review-heading {
                padding: 18px;
            }

            .review-summary {
                grid-template-columns: 1fr;
                gap: 11px;
            }

            .review-stat {
                padding: 15px;
            }

            .review-card {
                padding: 15px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="review-page">

        {{-- BACK BUTTON --}}
        <a href="{{ route('teacher.grade-submission') }}" class="back-button">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Grade Submission
        </a>

        {{-- ASSIGNMENT HEADER --}}
        <div class="review-heading">
            <h2>
                Grade {{ $assignment->grade_level }} -
                {{ $assignment->subject }}
            </h2>

            <p>
                <i class="fa-solid fa-calendar"></i>
                School Year: <strong>{{ $schoolYear }}</strong>
            </p>

            <p>
                Review the academic records and assessment results of your assigned students.
            </p>
        </div>

        @php
            $totalStudents = $students->count();

            $submittedCount = 0;
            $returnedCount = 0;
            $pendingCount = 0;

            $submittedStatuses = [
                'submitted',
                'forwarded_to_admin',
                'approved',
            ];

            foreach ($students as $student) {
                foreach ($terms as $term) {
                    $record = $student->gradeRecords->firstWhere('term', $term);

                    if (!$record) {
                        $pendingCount++;
                        continue;
                    }

                    if (in_array($record->status, $submittedStatuses, true)) {
                        $submittedCount++;
                    } elseif ($record->status === 'returned') {
                        $returnedCount++;
                    } else {
                        $pendingCount++;
                    }
                }
            }
        @endphp

        {{-- SUMMARY CARDS --}}
        <div class="review-summary">

            <div class="review-stat">
                <div class="review-stat-icon">
                    <i class="fa-solid fa-users"></i>
                </div>

                <div class="review-stat-info">
                    <span>Total Students</span>
                    <strong>{{ $totalStudents }}</strong>
                </div>
            </div>

            <div class="review-stat">
                <div class="review-stat-icon">
                    <i class="fa-solid fa-file-circle-check"></i>
                </div>

                <div class="review-stat-info">
                    <span>Submitted / Under Review</span>
                    <strong>{{ $submittedCount }}</strong>
                </div>
            </div>

            <div class="review-stat">
                <div class="review-stat-icon">
                    <i class="fa-solid fa-rotate-left"></i>
                </div>

                <div class="review-stat-info">
                    <span>Returned Records</span>
                    <strong>{{ $returnedCount }}</strong>
                </div>
            </div>

            <div class="review-stat">
                <div class="review-stat-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>

                <div class="review-stat-info">
                    <span>Pending / Draft Records</span>
                    <strong>{{ $pendingCount }}</strong>
                </div>
            </div>

        </div>

        {{-- STUDENT GRADES TABLE --}}
        <div class="review-card">

            <div class="review-card-header">
                <h3>
                    <i class="fa-solid fa-table-list"></i>
                    Student Academic Records
                </h3>

                <span class="student-count">
                    {{ $totalStudents }} Students
                </span>
            </div>

            @if ($students->isEmpty())

                <div class="empty-state">
                    <i class="fa-solid fa-folder-open"></i>
                    No active students found for this assignment and school year.
                </div>

            @else

                <div class="review-table-wrapper">
                    <table class="review-table">

                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Term 1</th>
                                <th>Term 2</th>
                                <th>Term 3</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($students as $student)

                                @php
                                    $studentTerms = [];

                                    foreach ($terms as $term) {
                                        $studentTerms[$term] = $student->gradeRecords->firstWhere('term', $term);
                                    }
                                @endphp

                                {{-- STUDENT ROW --}}
                                <tr class="student-row">

                                    <td>
                                        <span class="student-name">
                                            {{ $student->last_name }},
                                            {{ $student->first_name }}
                                            {{ $student->middle_name }}
                                        </span>

                                        <span class="student-id">
                                            {{ $student->student_id }}
                                        </span>
                                    </td>

                                    @foreach ($terms as $term)
                                        @php
                                            $record = $studentTerms[$term];
                                        @endphp

                                        <td>
                                            <div class="term-grade">

                                                @if ($record)

                                                    <span class="grade-value">
                                                        {{ $record->term_grade ?? '—' }}
                                                    </span>

                                                    @if ($record->descriptor)
                                                                <span class="descriptor-badge
                                                                                            {{ in_array($record->descriptor, ['Advancing', 'Benchmarking', 'Connecting'])
                                                        ? 'descriptor-passing'
                                                        : 'descriptor-failing' }}">
                                                                    {{ $record->descriptor }}
                                                                </span>
                                                    @endif

                                                    <span class="status-pill {{ str_replace('_', '-', $record->status) }}">
                                                        {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                                    </span>

                                                    <button type="button" class="details-toggle"
                                                        data-details="details-{{ $student->id }}-{{ $term }}">
                                                        <i class="fa-solid fa-eye"></i>
                                                        View Details
                                                    </button>

                                                @else

                                                    <span class="no-grade">—</span>

                                                    <span class="status-pill">
                                                        No Record
                                                    </span>

                                                @endif

                                            </div>
                                        </td>
                                    @endforeach

                                </tr>

                                {{-- ASSESSMENT DETAILS PER TERM --}}
                                @foreach ($terms as $term)

                                    @php
                                        $record = $studentTerms[$term];
                                    @endphp

                                    @if ($record)
                                        <tr class="details-row" id="details-{{ $student->id }}-{{ $term }}" hidden>
                                            <td colspan="4">

                                                <div class="details-container">

                                                    <div class="details-heading">
                                                        <strong>
                                                            <i class="fa-solid fa-clipboard-list"></i>
                                                            {{ $student->last_name }}, {{ $student->first_name }}
                                                            - Term {{ $term }} Assessment Breakdown
                                                        </strong>
                                                    </div>

                                                    {{-- RETURN FEEDBACK --}}
                                                    @if($record->adviser_return_reason || $record->admin_return_reason)

                                                        <div class="return-feedback-list">

                                                            @if($record->adviser_return_reason)
                                                                <div class="return-feedback adviser-feedback">
                                                                    <strong>
                                                                        <i class="fa-solid fa-user-tie"></i>
                                                                        Adviser Feedback
                                                                    </strong>

                                                                    <p>{{ $record->adviser_return_reason }}</p>
                                                                </div>
                                                            @endif

                                                            @if($record->admin_return_reason)
                                                                <div class="return-feedback admin-feedback">
                                                                    <strong>
                                                                        <i class="fa-solid fa-user-shield"></i>
                                                                        Administrator Feedback
                                                                    </strong>

                                                                    <p>{{ $record->admin_return_reason }}</p>
                                                                </div>
                                                            @endif

                                                        </div>

                                                    @endif

                                                    <div class="assessment-list">

                                                        @forelse ($record->assessmentScores as $assessment)

                                                            <div class="assessment-item">
                                                                <strong>
                                                                    {{ $assessment->assessment_code }}
                                                                    ({{ $assessment->assessment_group }})
                                                                </strong>

                                                                <span>
                                                                    {{ $assessment->score ?? '—' }}
                                                                    /
                                                                    {{ $assessment->highest_possible_score }}
                                                                </span>
                                                            </div>

                                                        @empty

                                                            <p class="empty-assessments">
                                                                No assessment scores available.
                                                            </p>

                                                        @endforelse

                                                    </div>

                                                </div>

                                            </td>
                                        </tr>
                                    @endif

                                @endforeach

                            @endforeach
                        </tbody>

                    </table>
                </div>

                <div class="review-note">
                    <i class="fa-solid fa-circle-info"></i>

                    <span>
                        Final Grade is not displayed because the official school policy
                        for computing the overall academic grade has not yet been finalized.
                        Individual term grades and assessment records are shown as stored.
                    </span>
                </div>

            @endif

        </div>

    </div>
@endsection

@section('page-js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('.details-toggle').forEach(button => {

                button.addEventListener('click', function () {

                    const targetId = this.dataset.details;
                    const target = document.getElementById(targetId);

                    if (!target) return;

                    const isHidden = target.hidden;

                    target.hidden = !isHidden;

                    this.innerHTML = isHidden
                        ? '<i class="fa-solid fa-eye-slash"></i> Hide Details'
                        : '<i class="fa-solid fa-eye"></i> View Details';

                });

            });

        });
    </script>
@endsection