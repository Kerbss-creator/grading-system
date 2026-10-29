@extends('layouts.teacher')

@section('title', 'My Subjects')
@section('header', 'My Subjects')
@section('header-description', 'View and manage the subjects assigned to you.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/teacher/teacher-subjects.css') }}">
@endsection

@section('content')
    @php
        $subjects = [
            [
                'name' => 'Mathematics',
                'code' => 'MATH-7',
                'grade' => 'Grade 7',
                'classes' => '1',
                'students' => '84 Students',
                'icon' => 'fa-calculator',
                'type' => 'mathematics',
            ],
            [
                'name' => 'Science',
                'code' => 'SCI-8',
                'grade' => 'Grade 8',
                'classes' => '1',
                'students' => '78 Students',
                'icon' => 'fa-flask',
                'type' => 'science',
            ],
            [
                'name' => 'English',
                'code' => 'ENG-9',
                'grade' => 'Grade 9',
                'classes' => '1',
                'students' => '81 Students',
                'icon' => 'fa-book',
                'type' => 'english',
            ],
            [
                'name' => 'Araling Panlipunan',
                'code' => 'AP-10',
                'grade' => 'Grade 10',
                'classes' => '1',
                'students' => '76 Students',
                'icon' => 'fa-earth-asia',
                'type' => 'ap',
            ],
        ];

        $selectedSchoolYear = request('school_year', '2026-2027');
        $selectedTerm = request('term', '1st-term');
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
                <strong>{{ count($subjects) }}</strong>
            </div>
        </div>

        <div class="subjects-grid">
            @foreach ($subjects as $subject)
                <a href="{{ url('/teacher/grade-management') }}?subject={{ urlencode($subject['code']) }}&school_year={{ urlencode($selectedSchoolYear) }}&term={{ urlencode($selectedTerm) }}"
                    class="subject-card-link" data-subject-code="{{ $subject['code'] }}"
                    aria-label="Open {{ $subject['name'] }} grading spreadsheet">
                    <article class="subject-card">

                        <div class="subject-card-top">
                            <div class="subject-icon {{ $subject['type'] }}">
                                <i class="fa-solid {{ $subject['icon'] }}"></i>
                            </div>
                            <span class="subject-code">{{ $subject['code'] }}</span>
                        </div>

                        <div class="subject-content">
                            <h2>{{ $subject['name'] }}</h2>

                            <p class="subject-level">
                                <i class="fa-solid fa-layer-group"></i>
                                {{ $subject['grade'] }}
                            </p>

                            <div class="subject-details">
                                <div class="detail-item">
                                    <i class="fa-solid fa-chalkboard"></i>
                                    <div>
                                        <span>Classes</span>
                                        <strong>{{ $subject['classes'] }}</strong>
                                    </div>
                                </div>

                                <div class="detail-item">
                                    <i class="fa-solid fa-users"></i>
                                    <div>
                                        <span>Students</span>
                                        <strong>{{ $subject['students'] }}</strong>
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
            @endforeach
        </div>
    </div>
@endsection

@section('page-js')
    <script src="{{ asset('js/teacher/teacher-subjects.js') }}" defer></script>
@endsection