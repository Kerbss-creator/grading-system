@extends('layouts.student')

@section('title', 'My Grades')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/student/student-grades.css') }}">
@endpush

@section('content')
    <div class="academic-report-page">

        <section class="academic-report">

            <header class="report-header">

                <img src="{{ asset('images/forbesologo.png') }}" alt="Forbes Academy Logo" class="report-school-logo">

                <h2>FORBES ACADEMY</h2>
                <p>Lakandula Drive, Legazpi City</p>

                <h3>Scholastic and Behavioral Progress Report</h3>

                @if($selectedTerm && $selectedSchoolYear)
                    <p class="report-period">
                        Term {{ $selectedTerm }} | School Year {{ $selectedSchoolYear }}
                    </p>
                @endif

            </header>

            <div class="learner-information">

                <div class="learner-info-item">
                    <span>Name of Learner:</span>
                    <strong>
                        {{ trim($student->first_name . ' ' . ($student->middle_name ?? '') . ' ' . $student->last_name) }}
                    </strong>
                </div>

                <div class="learner-info-item">
                    <span>Grade Level:</span>
                    <strong>{{ $student->grade_level }}</strong>
                </div>

            </div>

            @if(!$selectedSchoolYear)
                <div class="publication-notice">
                    <i class="fa-solid fa-circle-info"></i>
                    No active school year is currently set.
                </div>
            @elseif(!$isPublished)
                <div class="publication-notice">
                    <i class="fa-solid fa-clock"></i>
                    Grades for Term {{ $selectedTerm }} have not yet been published.
                </div>
            @endif

            <div class="report-tables">

                {{-- SCHOLASTIC PROGRESS --}}
                <section class="report-table-section">

                    <h4>Scholastic Progress</h4>

                    <table class="scholastic-table">

                        <thead>
                            <tr>
                                <th>Learning Area</th>
                                <th>Term Grade</th>
                            </tr>
                        </thead>

                        <tbody>

                            @php
                                $reportSubjects = [
                                    ['subject' => 'Filipino', 'component' => null],
                                    ['subject' => 'English', 'component' => null],
                                    ['subject' => 'Mathematics', 'component' => null],
                                    ['subject' => 'Science', 'component' => null],
                                    ['subject' => 'Araling Panlipunan', 'component' => null],
                                    ['subject' => 'TLE', 'component' => null],
                                    ['subject' => 'Values Education', 'component' => null],
                                    ['subject' => 'MAPEH', 'component' => 'Music and Arts'],
                                    ['subject' => 'MAPEH', 'component' => 'PE and Health'],
                                    ['subject' => 'Computer', 'component' => null],
                                ];
                            @endphp

                            @foreach($reportSubjects as $item)

                                                @php
                                                    $record = $grades->first(function ($grade) use ($item) {
                                                        $assignment = $grade->teachingAssignment;

                                                        if (!$assignment) {
                                                            return false;
                                                        }

                                                        $sameSubject = strtolower(trim($assignment->subject))
                                                            === strtolower($item['subject']);

                                                        if (!$sameSubject) {
                                                            return false;
                                                        }

                                                        if ($item['component'] !== null) {
                                                            return strtolower(trim($assignment->component ?? ''))
                                                                === strtolower($item['component']);
                                                        }

                                                        return true;
                                                    });
                                                @endphp

                                                <tr>
                                                    <td>
                                                        {{ $item['component'] ?? $item['subject'] }}
                                                    </td>

                                                    <td>
                                                        {{ $record && $record->term_grade !== null
                                ? number_format((float) $record->term_grade, 0)
                                : '—' }}
                                                    </td>
                                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </section>

                {{-- BEHAVIORAL PROGRESS --}}
                <section class="report-table-section">

                    <h4>Behavioral Progress</h4>

                    <table class="behavioral-table">

                        <thead>
                            <tr>
                                <th>Core Values</th>
                                <th>Rating</th>
                            </tr>
                        </thead>

                        <tbody>
                            @for($i = 0; $i < 8; $i++)
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            @endfor
                        </tbody>

                    </table>

                </section>

            </div>

            {{-- REPORT SIGNATORIES --}}
            <footer class="report-signatories">

                <div class="signatory">

                    <div class="signature-line"></div>

                    <strong>
                        {{ $adviser
        ? trim($adviser->first_name . ' ' . ($adviser->middle_name ?? '') . ' ' . $adviser->last_name)
        : 'Not Yet Assigned' }}
                    </strong>

                    <span>Class Adviser</span>

                </div>

                <div class="signatory">

                    <div class="signature-line"></div>

                    <strong>
                        {{ $administrator?->name ?? 'Not Yet Assigned' }}
                    </strong>

                    <span>Administrator</span>

                </div>

            </footer>

        </section>

    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/student/student-grades.js') }}"></script>
@endpush