@extends('layouts.teacher')

@section('title', 'My Subjects')

@section('header', 'My Subjects')

@section('header-description', 'View and manage the subjects assigned to you..')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/teacher/teacher-subjects.css') }}">
@endsection

@section('content')

    <div class="subjects-container">

        <!-- PAGE HEADER -->
        <div class="selection-card">

            <div class="selection-group">

                <label for="schoolYearSelect">School Year</label>

                <div class="select-wrapper">

                    <i class="fa-solid fa-calendar-days"></i>

                    <select id="schoolYearSelect">

                        <option value="2026-2027">
                            2026 - 2027
                        </option>

                        <option value="2027-2028">
                            2027 - 2028
                        </option>

                    </select>

                </div>

            </div>

            <div class="selection-group">

                <label for="termSelect">Term</label>

                <div class="select-wrapper">

                    <i class="fa-solid fa-book-open"></i>

                    <select id="termSelect">
                        <option value="1st-term">
                            1st Term
                        </option>

                        <option value="2nd-term">
                            2nd Term
                        </option>

                        <option value="3rd-term">
                            3rd Term
                        </option>
                    </select>
                </div>

            </div>
        </div>


        <!-- SUBJECT SUMMARY -->
        <div class="subject-summary">

            <div class="summary-icon">
                <i class="fa-solid fa-book-open"></i>
            </div>

            <div class="summary-info">
                <span>Assigned Subjects</span>
                <strong>4</strong>
            </div>

        </div>


        <!-- SUBJECT GRID -->
        <div class="subjects-grid">


            <!-- MATHEMATICS -->
            <div class="subject-card">

                <div class="subject-card-top">

                    <div class="subject-icon mathematics">
                        <i class="fa-solid fa-calculator"></i>
                    </div>

                    <span class="subject-code">MATH-7</span>

                </div>

                <div class="subject-content">

                    <h2>Mathematics</h2>

                    <p class="subject-level">
                        <i class="fa-solid fa-layer-group"></i>
                        Grade 7
                    </p>

                    <div class="subject-details">

                        <div class="detail-item">
                            <i class="fa-solid fa-chalkboard"></i>

                            <div>
                                <span>Classes</span>
                                <strong>7-A, 7-B</strong>
                            </div>
                        </div>

                        <div class="detail-item">
                            <i class="fa-solid fa-users"></i>

                            <div>
                                <span>Students</span>
                                <strong>84 Students</strong>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="subject-card-footer">

                    <a href="/teacher/classes" class="subject-btn">
                        View Classes
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                </div>

            </div>


            <!-- SCIENCE -->
            <div class="subject-card">

                <div class="subject-card-top">

                    <div class="subject-icon science">
                        <i class="fa-solid fa-flask"></i>
                    </div>

                    <span class="subject-code">SCI-8</span>

                </div>

                <div class="subject-content">

                    <h2>Science</h2>

                    <p class="subject-level">
                        <i class="fa-solid fa-layer-group"></i>
                        Grade 8
                    </p>

                    <div class="subject-details">

                        <div class="detail-item">
                            <i class="fa-solid fa-chalkboard"></i>

                            <div>
                                <span>Classes</span>
                                <strong>8-A, 8-C</strong>
                            </div>
                        </div>

                        <div class="detail-item">
                            <i class="fa-solid fa-users"></i>

                            <div>
                                <span>Students</span>
                                <strong>78 Students</strong>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="subject-card-footer">

                    <a href="/teacher/classes" class="subject-btn">
                        View Classes
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                </div>

            </div>


            <!-- ENGLISH -->
            <div class="subject-card">

                <div class="subject-card-top">

                    <div class="subject-icon english">
                        <i class="fa-solid fa-book"></i>
                    </div>

                    <span class="subject-code">ENG-9</span>

                </div>

                <div class="subject-content">

                    <h2>English</h2>

                    <p class="subject-level">
                        <i class="fa-solid fa-layer-group"></i>
                        Grade 9
                    </p>

                    <div class="subject-details">

                        <div class="detail-item">
                            <i class="fa-solid fa-chalkboard"></i>

                            <div>
                                <span>Classes</span>
                                <strong>9-A, 9-B</strong>
                            </div>
                        </div>

                        <div class="detail-item">
                            <i class="fa-solid fa-users"></i>

                            <div>
                                <span>Students</span>
                                <strong>81 Students</strong>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="subject-card-footer">

                    <a href="/teacher/classes" class="subject-btn">
                        View Classes
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                </div>

            </div>


            <!-- ARALING PANLIPUNAN -->
            <div class="subject-card">

                <div class="subject-card-top">

                    <div class="subject-icon ap">
                        <i class="fa-solid fa-globe-asia"></i>
                    </div>

                    <span class="subject-code">AP-10</span>

                </div>

                <div class="subject-content">

                    <h2>Araling Panlipunan</h2>

                    <p class="subject-level">
                        <i class="fa-solid fa-layer-group"></i>
                        Grade 10
                    </p>

                    <div class="subject-details">

                        <div class="detail-item">
                            <i class="fa-solid fa-chalkboard"></i>

                            <div>
                                <span>Classes</span>
                                <strong>10-A, 10-B</strong>
                            </div>
                        </div>

                        <div class="detail-item">
                            <i class="fa-solid fa-users"></i>

                            <div>
                                <span>Students</span>
                                <strong>76 Students</strong>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="subject-card-footer">

                    <a href="/teacher/classes" class="subject-btn">
                        View Classes
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                </div>

            </div>

        </div>

    </div>

@endsection


@section('page-js')
    <script src="{{ asset('js/teacher/teacher-subjects.js') }}"></script>
@endsection