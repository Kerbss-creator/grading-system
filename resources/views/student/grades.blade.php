@extends('layouts.student')

@section('title', 'My Grades | Forbes Academy')

@section('header', 'My Grades')
@section('header-description', 'View your final grades and academic performance.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/student/student-grades.css') }}">
@endsection

@section('content')
    <div class="grades-page">

        {{-- GRADE SUMMARY --}}
        <section class="grades-summary">

            <div class="grade-summary-card">
                <div class="summary-icon">
                    <i class="fa-solid fa-book-open"></i>
                </div>

                <div class="summary-info">
                    <p>Total Subjects</p>
                    <h2 id="totalSubjects">8</h2>
                    <span>Available subjects</span>
                </div>
            </div>

            <div class="grade-summary-card">
                <div class="summary-icon">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>

                <div class="summary-info">
                    <p>Selected Term</p>
                    <h2 id="summaryTerm">1st Term</h2>
                    <span id="summarySchoolYear">School Year 2026–2027</span>
                </div>
            </div>

            <div class="grade-summary-card">
                <div class="summary-icon">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>

                <div class="summary-info">
                    <p>My Section</p>
                    <h2>Grade 8 - Emerald</h2>
                    <span>Junior High School</span>
                </div>
            </div>

        </section>

        {{-- GRADES CONTENT --}}
        <section class="grades-panel">

            <div class="grades-panel-heading">
                <div>
                    <h2>Grade Overview</h2>
                    <p>Your final grades and remarks by subject.</p>
                </div>

                <div class="grades-status">
                    <i class="fa-solid fa-circle-check"></i>
                    Student View
                </div>
            </div>

            {{-- FILTERS --}}
            <div class="grades-filters">

                <div class="grades-search">
                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input type="search" id="subjectSearch" placeholder="Search subject..." aria-label="Search subjects">
                </div>

                <div class="filter-control">
                    <label for="schoolYearFilter">School Year</label>

                    <select id="schoolYearFilter">
                        <option value="2026-2027">2026–2027</option>
                        <option value="2027-2028">2027–2028</option>
                    </select>
                </div>

                <div class="filter-control">
                    <label for="termFilter">Grading Term</label>

                    <select id="termFilter">
                        <option value="1">1st Term</option>
                        <option value="2">2nd Term</option>
                        <option value="3">3rd Term</option>
                    </select>
                </div>

            </div>

            {{-- GRADES TABLE --}}
            <div class="grades-table-wrapper">

                <table class="grades-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Final Grade</th>
                            <th>Remarks</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody id="gradesTableBody">

                        {{-- SAMPLE DATA: Replace with database records later. --}}

                        <tr data-subject="Mathematics" data-term-1="90" data-term-2="92" data-term-3="91">
                            <td>
                                <div class="subject-cell">
                                    <div class="subject-icon">
                                        <i class="fa-solid fa-calculator"></i>
                                    </div>
                                    <div>
                                        <strong>Mathematics</strong>
                                        <span>Core Subject</span>
                                    </div>
                                </div>
                            </td>
                            <td class="grade-value">90</td>
                            <td><span class="remarks-text">Passed</span></td>
                            <td><span class="grade-status">Released</span></td>
                        </tr>

                        <tr data-subject="English" data-term-1="92" data-term-2="91" data-term-3="93">
                            <td>
                                <div class="subject-cell">
                                    <div class="subject-icon">
                                        <i class="fa-solid fa-book"></i>
                                    </div>
                                    <div>
                                        <strong>English</strong>
                                        <span>Core Subject</span>
                                    </div>
                                </div>
                            </td>
                            <td class="grade-value">92</td>
                            <td><span class="remarks-text">Passed</span></td>
                            <td><span class="grade-status">Released</span></td>
                        </tr>

                        <tr data-subject="Science" data-term-1="88" data-term-2="90" data-term-3="89">
                            <td>
                                <div class="subject-cell">
                                    <div class="subject-icon">
                                        <i class="fa-solid fa-flask"></i>
                                    </div>
                                    <div>
                                        <strong>Science</strong>
                                        <span>Core Subject</span>
                                    </div>
                                </div>
                            </td>
                            <td class="grade-value">88</td>
                            <td><span class="remarks-text">Passed</span></td>
                            <td><span class="grade-status">Released</span></td>
                        </tr>

                        <tr data-subject="Filipino" data-term-1="85" data-term-2="87" data-term-3="88">
                            <td>
                                <div class="subject-cell">
                                    <div class="subject-icon">
                                        <i class="fa-solid fa-language"></i>
                                    </div>
                                    <div>
                                        <strong>Filipino</strong>
                                        <span>Core Subject</span>
                                    </div>
                                </div>
                            </td>
                            <td class="grade-value">85</td>
                            <td><span class="remarks-text">Passed</span></td>
                            <td><span class="grade-status">Released</span></td>
                        </tr>

                        <tr data-subject="Araling Panlipunan" data-term-1="89" data-term-2="90" data-term-3="91">
                            <td>
                                <div class="subject-cell">
                                    <div class="subject-icon">
                                        <i class="fa-solid fa-earth-asia"></i>
                                    </div>
                                    <div>
                                        <strong>Araling Panlipunan</strong>
                                        <span>Core Subject</span>
                                    </div>
                                </div>
                            </td>
                            <td class="grade-value">89</td>
                            <td><span class="remarks-text">Passed</span></td>
                            <td><span class="grade-status">Released</span></td>
                        </tr>

                        <tr data-subject="TLE" data-term-1="91" data-term-2="92" data-term-3="90">
                            <td>
                                <div class="subject-cell">
                                    <div class="subject-icon">
                                        <i class="fa-solid fa-screwdriver-wrench"></i>
                                    </div>
                                    <div>
                                        <strong>TLE</strong>
                                        <span>Core Subject</span>
                                    </div>
                                </div>
                            </td>
                            <td class="grade-value">91</td>
                            <td><span class="remarks-text">Passed</span></td>
                            <td><span class="grade-status">Released</span></td>
                        </tr>

                        <tr data-subject="MAPEH" data-term-1="93" data-term-2="94" data-term-3="92">
                            <td>
                                <div class="subject-cell">
                                    <div class="subject-icon">
                                        <i class="fa-solid fa-music"></i>
                                    </div>
                                    <div>
                                        <strong>MAPEH</strong>
                                        <span>Core Subject</span>
                                    </div>
                                </div>
                            </td>
                            <td class="grade-value">93</td>
                            <td><span class="remarks-text">Passed</span></td>
                            <td><span class="grade-status">Released</span></td>
                        </tr>

                        <tr data-subject="Computer" data-term-1="90" data-term-2="91" data-term-3="92">
                            <td>
                                <div class="subject-cell">
                                    <div class="subject-icon">
                                        <i class="fa-solid fa-computer"></i>
                                    </div>
                                    <div>
                                        <strong>Computer</strong>
                                        <span>Core Subject</span>
                                    </div>
                                </div>
                            </td>
                            <td class="grade-value">90</td>
                            <td><span class="remarks-text">Passed</span></td>
                            <td><span class="grade-status">Released</span></td>
                        </tr>

                    </tbody>
                </table>

                <div class="grades-empty-state" id="gradesEmptyState" hidden>
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <h3>No subjects found</h3>
                    <p>Try another subject name or check your filters.</p>
                </div>

            </div>

            <div class="grades-panel-footer">
                <p>
                    <i class="fa-solid fa-circle-info"></i>
                    Only grades released by the school are displayed.
                </p>

                <span id="visibleSubjects">Showing 8 subjects</span>
            </div>

        </section>

    </div>
@endsection

@section('page-js')
    <script src="{{ asset('js/student/student-grades.js') }}"></script>
@endsection