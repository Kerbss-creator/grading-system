<!doctype html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'Forbes Academy Grading System')
    </title>


    <!-- GOOGLE FONT -->

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">


    <!-- FONT AWESOME -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


    <!-- GLOBAL CSS -->

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">


    <!-- TEACHER SIDEBAR CSS -->

    <link rel="stylesheet" href="{{ asset('css/teacher/teacher-sidebar.css') }}">


    <!-- PAGE CSS -->

    @yield('page-css')

</head>


<body>


    {{-- =====================================================
    TEACHER SIDEBAR
    ====================================================== --}}

    <aside class="teacher-sidebar">


        <!-- LOGO -->

        <div class="teacher-logo-section">

            <div class="teacher-logo">

                <img src="{{ asset('images/forbesologo.png') }}" alt="Forbes Academy Logo">

            </div>


            <div class="teacher-school-name">

                <h2>Forbes Academy</h2>

                <span>Grading System</span>

            </div>

        </div>



        <!-- NAVIGATION -->

        <nav class="teacher-navigation">


            <!-- MAIN -->

            <p class="teacher-nav-title">
                MAIN
            </p>


            <!-- DASHBOARD -->

            <a href="{{ url('/dashboard-teacher') }}"
                class="teacher-nav-link {{ request()->is('dashboard-teacher') ? 'active' : '' }}">

                <i class="fa-solid fa-gauge"></i>

                <span>My Dashboard</span>

            </a>



            <!-- MY CLASSES -->

            <a href="/teacher/classes" class="teacher-nav-link
               {{ request()->is('teacher/classes*') ? 'active' : '' }}">

                <i class="fa-solid fa-chalkboard"></i>

                <span>My Classes</span>

            </a>


            <!-- ACADEMIC -->

            <p class="teacher-nav-title">
                ACADEMIC
            </p>


            <!-- GRADE MANAGEMENT -->

            <a href="/teacher/grades" class="teacher-nav-link
               {{ request()->is('teacher/grades*') ? 'active' : '' }}">

                <i class="fa-solid fa-chart-column"></i>

                <span>Grade Management</span>

            </a>



            <!-- GRADE SUBMISSION -->

            <a href="/teacher/grade-submission" class="teacher-nav-link
               {{ request()->is('teacher/grade-submission*') ? 'active' : '' }}">

                <i class="fa-solid fa-file-arrow-up"></i>

                <span>Grade Submission</span>

            </a>



            <!-- MY SUBJECTS -->

            <a href="/teacher/subjects" class="teacher-nav-link
               {{ request()->is('teacher/subjects*') ? 'active' : '' }}">

                <i class="fa-solid fa-book-open"></i>

                <span>My Subjects</span>

            </a>



            <!-- SYSTEM -->

            <p class="teacher-nav-title">
                SYSTEM
            </p>


            <!-- ANNOUNCEMENTS -->

            <a href="/teacher/announcements" class="teacher-nav-link
               {{ request()->is('teacher/announcements*') ? 'active' : '' }}">

                <i class="fa-solid fa-bullhorn"></i>

                <span>Announcements</span>

            </a>



            <!-- MY PROFILE -->

            <a href="/teacher/profile" class="teacher-nav-link
               {{ request()->is('teacher/profile*') ? 'active' : '' }}">

                <i class="fa-solid fa-user"></i>

                <span>My Profile</span>

            </a>



            <!-- SETTINGS -->

            <a href="/teacher/settings" class="teacher-nav-link
               {{ request()->is('teacher/settings*') ? 'active' : '' }}">

                <i class="fa-solid fa-gear"></i>

                <span>Settings</span>

            </a>


        </nav>



        <!-- LOGOUT -->

        <div class="teacher-logout-section">

            <a href="/login" class="teacher-nav-link teacher-logout">

                <i class="fa-solid fa-right-from-bracket"></i>

                <span>Logout</span>

            </a>

        </div>


    </aside>



    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <main class="teacher-main-content">


        <!-- TOP HEADER -->

        <header class="teacher-top-header">


            <!-- HEADER TITLE -->

            <div class="teacher-header-title">

                <h1>

                    @yield('header', 'Dashboard')

                </h1>


                <p>

                    @yield(
                        'header-description',
                        'Welcome back, Teacher!'
                    )

                </p>

            </div>



            <!-- HEADER RIGHT -->

            <div class="teacher-header-right">


                <!-- NOTIFICATION -->

                <div class="notification-wrapper">


                    <button class="notification-btn" id="notificationBtn" type="button">

                        <i class="fa-regular fa-bell"></i>

                        <span class="notification-dot"></span>

                    </button>



                    <!-- NOTIFICATION PANEL -->

                    <div class="notification-panel" id="notificationPanel">


                        <div class="notification-header">


                            <div>

                                <h3>
                                    Notifications
                                </h3>

                                <span>
                                    0 unread
                                </span>

                            </div>


                            <button class="notification-close" id="notificationClose" type="button">

                                <i class="fa-solid fa-xmark"></i>

                            </button>


                        </div>



                        <!-- EMPTY NOTIFICATIONS -->

                        <div class="empty-notification">


                            <div class="empty-notification-icon">

                                <i class="fa-regular fa-bell-slash"></i>

                            </div>


                            <h4>
                                No notifications yet
                            </h4>


                            <p>
                                You don't have any notifications
                                at the moment.
                            </p>


                        </div>


                    </div>

                </div>



                <!-- TEACHER PROFILE -->

                <div class="profile-wrapper">


                    <button class="profile" id="profileBtn" type="button">


                        <div class="profile-icon">

                            <i class="fa-solid fa-chalkboard-user"></i>

                        </div>


                        <div class="profile-info">

                            <strong>
                                Teacher
                            </strong>

                            <span>
                                Teacher Account
                            </span>

                        </div>


                        <i class="fa-solid fa-chevron-down profile-arrow"></i>


                    </button>



                    <!-- PROFILE MENU -->

                    <div class="profile-menu" id="profileMenu">


                        <!-- PROFILE HEADER -->

                        <div class="profile-menu-header">


                            <div class="profile-menu-icon">

                                <i class="fa-solid fa-chalkboard-user"></i>

                            </div>


                            <div>

                                <strong>
                                    Teacher
                                </strong>

                                <span>
                                    Teacher Account
                                </span>

                            </div>


                        </div>



                        <div class="profile-menu-divider"></div>



                        <!-- MY PROFILE -->

                        <a href="/teacher/profile" class="profile-menu-item">

                            <i class="fa-solid fa-user"></i>

                            <span>
                                My Profile
                            </span>

                        </a>



                        <!-- SETTINGS -->

                        <a href="/teacher/settings" class="profile-menu-item">

                            <i class="fa-solid fa-gear"></i>

                            <span>
                                Settings
                            </span>

                        </a>



                        <!-- LOGOUT -->

                        <button type="button" class="profile-menu-item logout-item" id="logoutBtn">

                            <i class="fa-solid fa-right-from-bracket"></i>

                            <span>
                                Logout
                            </span>

                        </button>


                    </div>


                </div>


            </div>


        </header>



        <!-- PAGE CONTENT -->

        @yield('content')


    </main>



    <!-- SHARED JAVASCRIPT -->

    <script src="{{ asset('js/notification.js') }}"></script>

    <script src="{{ asset('js/profile.js') }}"></script>



    <!-- PAGE-SPECIFIC JAVASCRIPT -->

    @yield('page-js')


</body>

</html>