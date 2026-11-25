@extends('layouts.adviser')

@section('title', 'My Subjects')
@section('header', 'My Subjects')
@section('header-description', 'View and manage the subjects assigned to you.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/adviser/adviser-subjects.css') }}">
@endsection

@section('content')
    @php
        $subjectStyles = [
            'Mathematics' => ['code' => 'MATH', 'icon' => 'fa-calculator', 'type' => 'mathematics'],
            'Science' => ['code' => 'SCI', 'icon' => 'fa-flask', 'type' => 'science'],
            'English' => ['code' => 'ENG', 'icon' => 'fa-book', 'type' => 'english'],
            'Araling Panlipunan' => ['code' => 'AP', 'icon' => 'fa-earth-asia', 'type' => 'ap'],
            'TLE' => ['code' => 'TLE', 'icon' => 'fa-screwdriver-wrench', 'type' => 'tle'],
            'MAPEH' => ['code' => 'MAPEH', 'icon' => 'fa-music', 'type' => 'mapeh'],
            'Filipino' => ['code' => 'FIL', 'icon' => 'fa-book-open', 'type' => 'filipino'],
            'Values Education' => ['code' => 'VE', 'icon' => 'fa-heart', 'type' => 'values-education'],
            'Computer' => ['code' => 'COMP', 'icon' => 'fa-computer', 'type' => 'computer-education'],
            'Computer Education' => ['code' => 'COMP', 'icon' => 'fa-computer', 'type' => 'computer-education'],
        ];
    @endphp

    <div class="subjects-container">

        {{-- SCHOOL YEAR AND TERM FILTERS --}}
        <div class="selection-card">

            <div class="selection-group">
                <label for="schoolYearSelect">School Year</label>

                <div class="select-wrapper">
                    <i class="fa-solid fa-calendar-days"></i>

                    <select id="schoolYearSelect" name="school_year">
                        @foreach ($schoolYears as $year)
                            <option value="{{ $year->year_name }}" @selected($selectedSchoolYear === $year->year_name)>
                                {{ str_replace('-', ' - ', $year->year_name) }}

                                @if ($year->status === 'active')
                                    (Current)
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="selection-group">
                <label for="termSelect">Term</label>

                <div class="select-wrapper">
                    <i class="fa-solid fa-book-open"></i>

                    <select id="termSelect" name="term">
                        <option value="1" @selected($selectedTerm === 1)>
                            Term 1
                        </option>

                        <option value="2" @selected($selectedTerm === 2)>
                            Term 2
                        </option>

                        <option value="3" @selected($selectedTerm === 3)>
                            Term 3
                        </option>
                    </select>
                </div>
            </div>

        </div>

        {{-- ASSIGNED SUBJECT SUMMARY --}}
        <div class="subject-summary">

            <div class="summary-icon">
                <i class="fa-solid fa-book-open"></i>
            </div>

            <div class="summary-info">
                <span>Assigned Subjects</span>
                <strong>{{ $assignments->count() }}</strong>
            </div>

        </div>

        {{-- SUBJECT CARDS --}}
        <div class="subjects-grid">

            @forelse ($assignments as $assignment)

                @php
                    $style = $subjectStyles[$assignment->subject] ?? [
                        'code' => strtoupper(substr($assignment->subject, 0, 3)),
                        'icon' => 'fa-book',
                        'type' => 'default',
                    ];

                    $gradeNumber = preg_replace('/\D/', '', $assignment->grade_level);
                    $subjectCode = $style['code'] . '-' . $gradeNumber;
                @endphp

                <a href="{{ url('/adviser/grade-management') }}?assignment={{ $assignment->id }}&school_year={{ urlencode($selectedSchoolYear) }}&term={{ $selectedTerm }}"
                    class="subject-card-link" data-assignment-id="{{ $assignment->id }}" data-subject-code="{{ $subjectCode }}"
                    aria-label="Open {{ $assignment->subject }} grading spreadsheet">

                    <article class="subject-card">

                        <div class="subject-card-top">

                            <div class="subject-icon {{ $style['type'] }}">
                                <i class="fa-solid {{ $style['icon'] }}"></i>
                            </div>

                            <span class="subject-code">
                                {{ $subjectCode }}
                            </span>

                        </div>

                        <div class="subject-content">

                            <h2>{{ $assignment->subject }}</h2>

                            <p class="subject-level">
                                <i class="fa-solid fa-layer-group"></i>
                                {{ $assignment->grade_level }}
                            </p>

                            <div class="subject-details">

                                <div class="detail-item">
                                    <i class="fa-solid fa-chalkboard"></i>

                                    <div>
                                        <span>Classes</span>
                                        <strong>1</strong>
                                    </div>
                                </div>

                                <div class="detail-item">
                                    <i class="fa-solid fa-users"></i>

                                    <div>
                                        <span>Students</span>
                                        <strong>{{ $assignment->student_count }} Students</strong>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <div class="subject-card-footer">

                            <span class="subject-btn">
                                View Gradebook
                                <i class="fa-solid fa-arrow-right"></i>
                            </span>

                        </div>

                    </article>

                </a>

            @empty

                <div class="empty-state">

                    <i class="fa-solid fa-book-open"></i>

                    <h3>No Subjects Assigned Yet</h3>

                    <p>
                        Your assigned subjects will appear here once
                        the administrator assigns them to you.
                    </p>

                </div>

            @endforelse

        </div>

    </div>
@endsection

@section('page-js')
    <script src="{{ asset('js/adviser/adviser-subjects.js') }}" defer></script>
@endsection