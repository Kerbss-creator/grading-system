<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Forbes Academy Grading System')</title>

    <!-- GOOGLE FONT -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- FONT AWESOME -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- GLOBAL CSS -->
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">

    <!-- STUDENT SIDEBAR CSS -->
    <link rel="stylesheet" href="{{ asset('css/student/student-sidebar.css') }}">

    <!-- PAGE CSS -->
    @yield('page-css')
</head>

<body>

    {{-- STUDENT SIDEBAR --}}
    <aside class="student-sidebar">

        <!-- LOGO -->
        <div class="student-logo-section">
            <div class="student-logo">
                <img src="{{ asset('images/forbesologo.png') }}" alt="Forbes Academy Logo">
            </div>

            <div class="student-school-name">
                <h2>Forbes Academy</h2>
                <span>Grading System</span>
            </div>
        </div>

        <!-- NAVIGATION -->
        <nav class="student-navigation">

            <p class="student-nav-title">MAIN</p>

            <!-- DASHBOARD -->
            <a href="{{ url('/student/dashboard') }}"
                class="student-nav-link {{ request()->is('student/dashboard*') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge"></i>
                <span>My Dashboard</span>
            </a>

            <!-- MY CLASSES -->
            <a href="{{ url('/student/classes') }}"
                class="student-nav-link {{ request()->is('student/classes*') ? 'active' : '' }}">
                <i class="fa-solid fa-book-open"></i>
                <span>My Classes</span>
            </a>

            <!-- MY GRADES -->
            <a href="{{ url('/student/grades') }}"
                class="student-nav-link {{ request()->is('student/grades*') ? 'active' : '' }}">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>My Grades</span>
            </a>

            <!-- SYSTEM -->
            <p class="student-nav-title">SYSTEM</p>

            <!-- ANNOUNCEMENTS -->
            <a href="{{ url('/student/announcements') }}"
                class="student-nav-link {{ request()->is('student/announcements*') ? 'active' : '' }}">
                <i class="fa-solid fa-bullhorn"></i>
                <span>Announcements</span>
            </a>

            <!-- MY PROFILE -->
            <a href="{{ url('/student/profile') }}"
                class="student-nav-link {{ request()->is('student/profile*') ? 'active' : '' }}">
                <i class="fa-solid fa-user"></i>
                <span>My Profile</span>
            </a>

            <!-- SETTINGS -->
            <a href="{{ url('/student/settings') }}"
                class="student-nav-link {{ request()->is('student/settings*') ? 'active' : '' }}">
                <i class="fa-solid fa-gear"></i>
                <span>Settings</span>
            </a>

        </nav>

        <!-- LOGOUT -->
        <div class="student-logout-section">
            <a href="{{ url('/login') }}" class="student-nav-link student-logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>

    </aside>

    {{-- MAIN CONTENT --}}
    <main class="student-main-content">

        <!-- TOP HEADER -->
        <header class="student-top-header">

            <!-- HEADER TITLE -->
            <div class="student-header-title">
                <h1>@yield('header', 'My Dashboard')</h1>

                <p>
                    @yield('header-description', 'Welcome back, Student!')
                </p>
            </div>

            <!-- HEADER RIGHT -->
            <div class="student-header-right">

                <!-- NOTIFICATIONS -->
                <div class="notification-wrapper">

                    <button class="notification-btn" id="notificationBtn" type="button" aria-label="Open notifications"
                        aria-expanded="false">
                        <i class="fa-regular fa-bell"></i>
                        <span class="notification-dot"></span>
                    </button>

                    <!-- NOTIFICATION PANEL -->
                    <div class="notification-panel" id="notificationPanel">

                        <div class="notification-header">
                            <div>
                                <h3>Notifications</h3>
                                <span>0 unread</span>
                            </div>

                            <button class="notification-close" id="notificationClose" type="button"
                                aria-label="Close notifications">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <!-- EMPTY NOTIFICATIONS -->
                        <div class="empty-notification">
                            <div class="empty-notification-icon">
                                <i class="fa-regular fa-bell-slash"></i>
                            </div>

                            <h4>No notifications yet</h4>

                            <p>
                                You don't have any notifications
                                at the moment.
                            </p>
                        </div>

                    </div>
                </div>

                <!-- STUDENT PROFILE -->
                <div class="profile-wrapper">

                    <button class="profile" id="profileBtn" type="button" aria-label="Open student profile menu"
                        aria-expanded="false">

                        <div class="profile-icon">
                            <i class="fa-solid fa-user-graduate"></i>
                        </div>

                        <div class="profile-info">
                            <strong>Student</strong>
                            <span>Student Account</span>
                        </div>

                        <i class="fa-solid fa-chevron-down profile-arrow"></i>
                    </button>

                    <!-- PROFILE MENU -->
                    <div class="profile-menu" id="profileMenu">

                        <div class="profile-menu-header">
                            <div class="profile-menu-icon">
                                <i class="fa-solid fa-user-graduate"></i>
                            </div>

                            <div>
                                <strong>Student</strong>
                                <span>Student Account</span>
                            </div>
                        </div>

                        <div class="profile-menu-divider"></div>

                        <!-- MY PROFILE -->
                        <a href="{{ url('/student/profile') }}" class="profile-menu-item">
                            <i class="fa-solid fa-user"></i>
                            <span>My Profile</span>
                        </a>

                        <!-- SETTINGS -->
                        <a href="{{ url('/student/settings') }}" class="profile-menu-item">
                            <i class="fa-solid fa-gear"></i>
                            <span>Settings</span>
                        </a>

                        <!-- LOGOUT -->
                        <button type="button" class="profile-menu-item logout-item" id="logoutBtn">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Logout</span>
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