@extends('layouts.teacher')

@section('title', 'My Subjects')
@section('header', 'My Subjects')
@section('header-description', 'View and manage the subjects assigned to you.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/teacher/teacher-subjects.css') }}">
@endsection

@section('content')
    @php
        $selectedSchoolYear = request('school_year', '2026-2027');
        $selectedTerm = request('term', '1st-term');

        $subjectStyles = [
            'Mathematics' => ['code' => 'MATH', 'icon' => 'fa-calculator', 'type' => 'mathematics'],
            'Science' => ['code' => 'SCI', 'icon' => 'fa-flask', 'type' => 'science'],
            'English' => ['code' => 'ENG', 'icon' => 'fa-book', 'type' => 'english'],
            'Araling Panlipunan' => ['code' => 'AP', 'icon' => 'fa-earth-asia', 'type' => 'ap'],
            'TLE' => ['code' => 'TLE', 'icon' => 'fa-screwdriver-wrench', 'type' => 'tle'],
            'MAPEH' => ['code' => 'MAPEH', 'icon' => 'fa-music', 'type' => 'mapeh'],
            'Filipino' => ['code' => 'FIL', 'icon' => 'fa-book-open', 'type' => 'filipino'],
            'Values Education' => ['code' => 'VE', 'icon' => 'fa-heart', 'type' => 'values-education'],
            'Computer Education' => ['code' => 'COMP', 'icon' => 'fa-computer', 'type' => 'computer-education'],
        ];
    @endphp

    <div class="subjects-container">

        <div class="selection-card">
            <div class="selection-group">
                <label for="schoolYearSelect">School Year</label>
                <div class="select-wrapper">
                    <i class="fa-solid fa-calendar-days"></i>
                    <select id="schoolYearSelect" name="school_year">
                        <option value="2026-2027" @selected($selectedSchoolYear === '2026-2027')>
                            2026 - 2027
                        </option>
                        <option value="2027-2028" @selected($selectedSchoolYear === '2027-2028')>
                            2027 - 2028
                        </option>
                    </select>
                </div>
            </div>

            <div class="selection-group">
                <label for="termSelect">Term</label>
                <div class="select-wrapper">
                    <i class="fa-solid fa-book-open"></i>
                    <select id="termSelect" name="term">
                        <option value="1st-term" @selected($selectedTerm === '1st-term')>
                            1st Term
                        </option>
                        <option value="2nd-term" @selected($selectedTerm === '2nd-term')>
                            2nd Term
                        </option>
                        <option value="3rd-term" @selected($selectedTerm === '3rd-term')>
                            3rd Term
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <div class="subject-summary">
            <div class="summary-icon">
                <i class="fa-solid fa-book-open"></i>
            </div>
            <div class="summary-info">
                <span>Assigned Subjects</span>
                <strong>{{ $assignments->count() }}</strong>
            </div>
        </div>

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

                <a href="{{ url('/teacher/grade-management') }}?assignment={{ $assignment->id }}&school_year={{ urlencode($selectedSchoolYear) }}&term={{ urlencode($selectedTerm) }}"
                    class="subject-card-link" data-assignment-id="{{ $assignment->id }}" data-subject-code="{{ $subjectCode }}"
                    aria-label="Open {{ $assignment->subject }} grading spreadsheet">

                    <article class="subject-card">

                        <div class="subject-card-top">
                            <div class="subject-icon {{ $style['type'] }}">
                                <i class="fa-solid {{ $style['icon'] }}"></i>
                            </div>

                            <span class="subject-code">{{ $subjectCode }}</span>
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
                    <p>Your assigned subjects will appear here once the administrator assigns them to you.</p>
                </div>

            @endforelse
        </div>
    </div>
@endsection

@section('page-js')
    <script src="{{ asset('js/teacher/teacher-subjects.js') }}" defer></script>
@endsection