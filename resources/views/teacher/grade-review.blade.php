@extends('layouts.teacher')

@section('title', 'Grade Review - Forbes Academy')

@section('header', 'Grade Review')

@section('header-description')
    Review individual student grades and assessment records.
@endsection

@section('page-css')
    <style>
        .review-page {
            padding: 5px 0 30px;
        }

        .review-heading {
            background: #fff;
            padding: 24px;
            border-radius: 14px;
            border-left: 5px solid #036f35;
            margin-bottom: 22px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .04);
        }

        .review-heading h2 {
            margin: 0 0 8px;
            color: #183b29;
            font-size: 21px;
        }

        .review-heading p {
            margin: 4px 0;
            color: #68756d;
            font-size: 13px;
        }

        .review-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .review-stat {
            background: #fff;
            padding: 20px;
            border-radius: 13px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .04);
        }

        .review-stat span {
            display: block;
            color: #778078;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .review-stat strong {
            color: #036f35;
            font-size: 27px;
        }

        .review-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .review-tab {
            border: 1px solid #dce5de;
            background: #fff;
            color: #536158;
            padding: 11px 20px;
            border-radius: 9px;
            font-family: inherit;
            font-weight: 600;
            cursor: pointer;
            transition: .2s;
        }

        .review-tab:hover,
        .review-tab.active {
            background: #036f35;
            color: #fff;
            border-color: #036f35;
        }

        .review-panel {
            display: none;
            background: #fff;
            padding: 22px;
            border-radius: 14px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, .04);
        }

        .review-panel.active {
            display: block;
        }

        .review-panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .review-panel-header h3 {
            margin: 0;
            color: #183b29;
            font-size: 17px;
        }

        .review-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .review-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        .review-table th {
            background: #f2f7f3;
            color: #315b40;
            padding: 13px 12px;
            text-align: left;
            font-size: 12px;
            white-space: nowrap;
        }

        .review-table td {
            padding: 13px 12px;
            border-bottom: 1px solid #edf0ed;
            color: #46534a;
            font-size: 13px;
            vertical-align: middle;
        }

        .review-table tbody tr:hover {
            background: #f9fcfa;
        }

        .student-name {
            font-weight: 600;
            color: #243c2c;
        }

        .student-id {
            display: block;
            font-size: 11px;
            color: #879188;
            margin-top: 3px;
        }

        .status-pill {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: capitalize;
            background: #f0f2f0;
            color: #647067;
        }

        .status-pill.submitted,
        .status-pill.approved,
        .status-pill.forwarded {
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

        .details-row td {
            background: #f8fbf8;
            padding: 16px;
        }

        .assessment-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .assessment-item {
            border: 1px solid #e2eae3;
            background: #fff;
            padding: 10px 13px;
            border-radius: 8px;
            min-width: 110px;
        }

        .assessment-item strong {
            display: block;
            color: #315b40;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .assessment-item span {
            font-size: 13px;
            color: #56635a;
        }

        .details-toggle {
            border: 0;
            background: transparent;
            color: #036f35;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }

        .empty-state {
            padding: 35px 15px;
            text-align: center;
            color: #849087;
            font-size: 13px;
        }

        .pending-final {
            color: #a27a16;
            font-weight: 600;
        }

        .summary-note {
            color: #7b867e;
            font-size: 12px;
            margin-top: 12px;
        }

        @media (max-width: 768px) {
            .review-page {
                padding: 18px 14px;
            }

            .review-summary {
                grid-template-columns: 1fr;
            }

            .review-panel {
                padding: 15px;
            }
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 11px 17px;
            margin-bottom: 18px;
            background: #fff;
            color: #036f35;
            border: 1px solid #dce5de;
            border-radius: 9px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: .2s ease;
        }

        .back-button:hover {
            background: #036f35;
            color: #fff;
            border-color: #036f35;
            transform: translateX(-3px);
        }

        .descriptor-badge {
            display: inline-block;
            padding: 5px 11px;
            border-radius: 20px;
            font-size: 12px;
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
    </style>
@endsection

@section('content')
    <div class="review-page">



        <a href="{{ route('teacher.grade-submission') }}" class="back-button">
            <i class="fa-solid fa-arrow-left"></i>
            Back to Grade Submission
        </a>

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
                Review the individual academic records of your assigned students.
            </p>
        </div>

        @php
            $totalStudents = $students->count();
            $submittedCount = 0;
            $incompleteCount = 0;

            foreach ($students as $student) {
                $record = $student->gradeRecords->firstWhere('term', $selectedTerm);

                if ($record && $record->status !== 'draft') {
                    $submittedCount++;
                } else {
                    $incompleteCount++;
                }
            }
        @endphp

        <div class="review-summary">
            <div class="review-stat">
                <span>Total Students</span>
                <strong>{{ $totalStudents }}</strong>
            </div>

            <div class="review-stat">
                <span>Records Submitted - Selected Term</span>
                <strong>{{ $submittedCount }}</strong>
            </div>

            <div class="review-stat">
                <span>Pending / Draft Records</span>
                <strong>{{ $incompleteCount }}</strong>
            </div>
        </div>

        <div class="review-tabs">
            @foreach ($terms as $term)
                <button type="button" class="review-tab {{ $selectedTerm === $term ? 'active' : '' }}"
                    data-tab="term-{{ $term }}">
                    Term {{ $term }}
                </button>
            @endforeach

            <button type="button" class="review-tab" data-tab="summary">

                Summary
            </button>
        </div>

        {{-- TERM 1, 2 AND 3 --}}
        @foreach ($terms as $term)
            <section class="review-panel {{ $selectedTerm === $term ? 'active' : '' }}" id="term-{{ $term }}">
                <div class="review-panel-header">
                    <h3>Term {{ $term }} - Student Grades</h3>
                    <span>{{ $totalStudents }} Students</span>
                </div>

                @if ($students->isEmpty())
                    <div class="empty-state">
                        No active students found for this assignment and school year.
                    </div>
                @else
                    <div class="review-table-wrapper">
                        <table class="review-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>WW PS</th>
                                    <th>PT PS</th>
                                    <th>EX PS</th>
                                    <th>Initial Grade</th>
                                    <th>Term Grade</th>
                                    <th>Descriptor</th>
                                    <th>Status</th>
                                    <th>Details</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($students as $student)
                                    @php
                                        $record = $student->gradeRecords->firstWhere('term', $term);
                                    @endphp

                                    <tr>
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

                                        <td>{{ $record?->ww_ps ?? '—' }}</td>
                                        <td>{{ $record?->pt_ps ?? '—' }}</td>
                                        <td>{{ $record?->ex_ps ?? '—' }}</td>
                                        <td>{{ $record?->initial_grade ?? '—' }}</td>
                                        <td>{{ $record?->term_grade ?? '—' }}</td>
                                        <td>
                                            @if($record?->descriptor)
                                            <span class="descriptor-badge
                                            {{ in_array($record->descriptor, ['Advancing', 'Benchmarking', 'Connecting'])
                                            ? 'descriptor-passing'
                                            : 'descriptor-failing' }}">
                                            {{ $record->descriptor }}
                                            </span>
                                            @else
    
                                            @endif
                                        </td>

                                        <td>
                                            @if ($record)
                                                <span class="status-pill {{ $record->status }}">
                                                    {{ ucfirst($record->status) }}
                                                </span>
                                            @else
                                                <span class="status-pill">
                                                    No Record
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($record)
                                                <button type="button" class="details-toggle"
                                                    data-details="details-{{ $term }}-{{ $student->id }}">
                                                    View Details
                                                </button>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>

                                    @if ($record)
                                        <tr class="details-row" id="details-{{ $term }}-{{ $student->id }}" hidden>
                                            <td colspan="9">
                                                <strong>Assessment Breakdown</strong>

                                                <div class="assessment-list" style="margin-top: 12px;">
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
                                                        <p>No assessment scores available.</p>
                                                    @endforelse
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endforeach

        {{-- SUMMARY --}}
        <section class="review-panel" id="summary">
            <div class="review-panel-header">
                <h3>Academic Records Summary</h3>
                <span>Terms 1–3</span>
            </div>

            @if ($students->isEmpty())
                <div class="empty-state">
                    No student records available.
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
                                <th>Final Grade</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($students as $student)
                                @php
                                    $term1 = $student->gradeRecords->firstWhere('term', 1);
                                    $term2 = $student->gradeRecords->firstWhere('term', 2);
                                    $term3 = $student->gradeRecords->firstWhere('term', 3);
                                @endphp

                                <tr>
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

                                    <td>{{ $term1?->term_grade ?? '—' }}</td>
                                    <td>{{ $term2?->term_grade ?? '—' }}</td>
                                    <td>{{ $term3?->term_grade ?? '—' }}</td>

                                    <td>
                                        <span class="pending-final">
                                            Pending
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="summary-note">
                    Final Grade computation will be implemented after the official
                    school grading policy is confirmed.
                </p>
            @endif
        </section>

    </div>
@endsection

@section('page-js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // Switch between term tabs and summary
            const tabs = document.querySelectorAll('.review-tab');
            const panels = document.querySelectorAll('.review-panel');

            tabs.forEach(tab => {
                tab.addEventListener('click', function () {
                    const target = this.dataset.tab;

                    tabs.forEach(item => item.classList.remove('active'));
                    panels.forEach(panel => panel.classList.remove('active'));

                    this.classList.add('active');

                    const selectedPanel = document.getElementById(target);

                    if (selectedPanel) {
                        selectedPanel.classList.add('active');
                    }
                });
            });

            // Show and hide assessment breakdown
            document.querySelectorAll('.details-toggle').forEach(button => {
                button.addEventListener('click', function () {
                    const target = document.getElementById(this.dataset.details);

                    if (!target) return;

                    const isHidden = target.hidden;

                    target.hidden = !isHidden;
                    this.textContent = isHidden ? 'Hide Details' : 'View Details';
                });
            });

        });
    </script>
@endsection