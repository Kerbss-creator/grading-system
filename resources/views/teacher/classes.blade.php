@extends('layouts.teacher')

@section('title', 'My Classes')

@section('header', 'My Classes')

@section('header-description', 'View and manage your assigned classes.')

@section('page-css')
    <link rel="stylesheet" href="{{ asset('css/teacher/teacher-classes.css') }}">
@endsection

@section('content')

    <div class="classes-container">

        <div class="school-info">
                <span>School Year</span>
                <strong>2026-2027</strong>
                <small>1st Term</small>
            </div>
        </div>


        {{-- SUMMARY CARDS --}}
        <div class="class-summary">

            <div class="summary-card">
                <div class="summary-icon">
                    <i class="fa-solid fa-chalkboard"></i>
                </div>

                <div class="summary-info">
                    <span>Total Classes</span>
                    <h3>3</h3>
                </div>
            </div>


            <div class="summary-card">
                <div class="summary-icon">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>

                <div class="summary-info">
                    <span>Total Students</span>
                    <h3>84</h3>
                </div>
            </div>


            <div class="summary-card">
                <div class="summary-icon">
                    <i class="fa-solid fa-book"></i>
                </div>

                <div class="summary-info">
                    <span>Subjects</span>
                    <h3>4</h3>
                </div>
            </div>

        </div>


        {{-- CLASS SECTION --}}
        <div class="classes-section">

            <div class="section-heading">
                <div>
                    <h2>Assigned Classes</h2>
                    <p>Your classes for the current school year.</p>
                </div>

                <div class="class-tools">

                    <div class="search-box">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="classSearch" placeholder="Search class...">
                    </div>

                    <select id="gradeFilter">
                        <option value="all">All Grades</option>
                        <option value="7">Grade 7</option>
                        <option value="8">Grade 8</option>
                        <option value="9">Grade 9</option>
                        <option value="10">Grade 10</option>
                    </select>

                </div>
            </div>


            {{-- CLASS CARDS --}}
            <div class="classes-grid" id="classesGrid">

                {{-- CLASS 1 --}}
                <div class="class-card" data-grade="7" data-search="grade 7 section a">

                    <div class="class-card-header">
                        <div class="class-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>

                        <span class="class-status">
                            Active
                        </span>
                    </div>

                    <div class="class-card-body">

                        <span class="grade-label">GRADE 7</span>

                        <h3>Grade 7 - A</h3>

                        <p class="school-year">
                            <i class="fa-regular fa-calendar"></i>
                            School Year 2026–2027
                        </p>

                        <div class="class-details">

                            <div>
                                <i class="fa-solid fa-user-graduate"></i>
                                <span>
                                    <strong>28</strong>
                                    Students
                                </span>
                            </div>

                            <div>
                                <i class="fa-solid fa-book"></i>
                                <span>
                                    <strong>2</strong>
                                    Subjects
                                </span>
                            </div>

                        </div>

                    </div>

                    <div class="class-card-footer">

                        <button class="view-class-btn" data-class="Grade 7 - A" data-grade="Grade 7" data-students="28"
                            data-subjects="Mathematics, Science">
                            View Class
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>

                    </div>

                </div>


                {{-- CLASS 2 --}}
                <div class="class-card" data-grade="8" data-search="grade 8 section b">

                    <div class="class-card-header">

                        <div class="class-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>

                        <span class="class-status">
                            Active
                        </span>

                    </div>

                    <div class="class-card-body">

                        <span class="grade-label">GRADE 8</span>

                        <h3>Grade 8 - A</h3>

                        <p class="school-year">
                            <i class="fa-regular fa-calendar"></i>
                            School Year 2026-2027
                        </p>

                        <div class="class-details">

                            <div>
                                <i class="fa-solid fa-user-graduate"></i>
                                <span>
                                    <strong>27</strong>
                                    Students
                                </span>
                            </div>

                            <div>
                                <i class="fa-solid fa-book"></i>
                                <span>
                                    <strong>1</strong>
                                    Subject
                                </span>
                            </div>

                        </div>

                    </div>

                    <div class="class-card-footer">

                        <button class="view-class-btn" data-class="Grade 8 - B" data-grade="Grade 8" data-students="27"
                            data-subjects="English">
                            View Class
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>

                    </div>

                </div>


                {{-- CLASS 3 --}}
                <div class="class-card" data-grade="9" data-search="grade 9 section c">

                    <div class="class-card-header">

                        <div class="class-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>

                        <span class="class-status">
                            Active
                        </span>

                    </div>

                    <div class="class-card-body">

                        <span class="grade-label">GRADE 9</span>

                        <h3>Grade 9 - A</h3>

                        <p class="school-year">
                            <i class="fa-regular fa-calendar"></i>
                            School Year 2026-2027
                        </p>

                        <div class="class-details">

                            <div>
                                <i class="fa-solid fa-user-graduate"></i>
                                <span>
                                    <strong>29</strong>
                                    Students
                                </span>
                            </div>

                            <div>
                                <i class="fa-solid fa-book"></i>
                                <span>
                                    <strong>1</strong>
                                    Subject
                                </span>
                            </div>

                        </div>

                    </div>

                    <div class="class-card-footer">

                        <button class="view-class-btn" data-class="Grade 9 - C" data-grade="Grade 9" data-students="29"
                            data-subjects="Filipino">
                            View Class
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>

                    </div>

                </div>

            </div>


            {{-- NO RESULTS --}}
            <div class="no-classes" id="noClasses">

                <i class="fa-solid fa-folder-open"></i>

                <h3>No Classes Found</h3>

                <p>
                    No classes match your search or selected grade level.
                </p>

            </div>

        </div>

    </div>


    {{-- CLASS DETAILS MODAL --}}
    <div class="class-modal-overlay" id="classModal">

        <div class="class-modal">

            <button class="close-modal" id="closeClassModal">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="modal-icon">
                <i class="fa-solid fa-users"></i>
            </div>

            <span id="modalGrade">GRADE 7</span>

            <h2 id="modalClass">Grade 7 - A</h2>

            <p class="modal-school-year">
                School Year 2026-2027
            </p>

            <div class="modal-details">

                <div class="modal-detail">
                    <i class="fa-solid fa-user-graduate"></i>

                    <div>
                        <span>Students</span>
                        <strong id="modalStudents">28</strong>
                    </div>
                </div>


                <div class="modal-detail">
                    <i class="fa-solid fa-book"></i>

                    <div>
                        <span>Subjects</span>
                        <strong id="modalSubjects">
                            Mathematics, Science
                        </strong>
                    </div>
                </div>

            </div>

            <button class="modal-action" id="manageClassBtn">
                Manage Class
                <i class="fa-solid fa-arrow-right"></i>
            </button>

        </div>

    </div>

@endsection


@section('page-js')
    <script src="{{ asset('js/teacher/teacher-classes.js') }}"></script>
@endsection