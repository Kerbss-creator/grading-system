<!doctype html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'Forbes Academy Grading System')
    </title>

    {{-- GOOGLE FONT --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- FONT AWESOME --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    {{-- GLOBAL CSS --}}
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">

    {{-- ADVISER SIDEBAR CSS --}}
    <link rel="stylesheet" href="{{ asset('css/adviser/adviser-sidebar.css') }}">

    {{-- PAGE CSS --}}
    @yield('page-css')

</head>

<body>

    {{-- ADVISER SIDEBAR --}}

    <aside class="adviser-sidebar">

        {{-- LOGO --}}

        <div class="adviser-logo-section">

            <div class="adviser-logo">

                <img src="{{ asset('images/forbesologo.png') }}" alt="Forbes Academy Logo">

            </div>

            <div class="adviser-school-name">

                <h2>Forbes Academy</h2>

                <span>Grading System</span>

            </div>

        </div>

        {{-- NAVIGATION --}}

        <nav class="adviser-navigation">

            {{-- MAIN --}}

            <p class="adviser-nav-title">MAIN</p>

            {{-- DASHBOARD --}}

            <a href="{{ url('/adviser/dashboard') }}"
                class="adviser-nav-link {{ request()->is('adviser/dashboard*') ? 'active' : '' }}">

                <i class="fa-solid fa-gauge"></i>

                <span>My Dashboard</span>

            </a>

            {{-- MY CLASSES --}}

            <a href="{{ url('/adviser/classes') }}"
                class="adviser-nav-link {{ request()->is('adviser/classes*') ? 'active' : '' }}">

                <i class="fa-solid fa-chalkboard"></i>

                <span>My Classes</span>

            </a>

            {{-- MY STUDENTS --}}

            <a href="{{ url('/adviser/students') }}"
                class="adviser-nav-link {{ request()->is('adviser/students*') ? 'active' : '' }}">

                <i class="fa-solid fa-users"></i>

                <span>My Students</span>

            </a>


            {{-- ACADEMIC --}}

            <p class="adviser-nav-title">ACADEMIC</p>

            {{-- ACADEMIC RECORDS DROPDOWN --}}

            <div class="adviser-nav-dropdown {{ request()->is('adviser/records*') ? 'open' : '' }}">

                <button type="button"
                    class="adviser-nav-link adviser-dropdown-toggle {{ request()->is('adviser/records*') ? 'active' : '' }}"
                    id="academicRecordsToggle"
                    aria-expanded="{{ request()->is('adviser/records*') ? 'true' : 'false' }}"
                    aria-controls="academicRecordsSubmenu">

                    <i class="fa-solid fa-folder-open"></i>

                    <span>Grade Loads</span>

                    <i class="fa-solid fa-chevron-down adviser-dropdown-arrow"></i>

                </button>

                {{-- SUBJECTS SUBMENU --}}

                <div class="adviser-submenu" id="academicRecordsSubmenu">

                   <a href="{{ url('/adviser/academic-records') }}"
                class="adviser-nav-link {{ request()->is('adviser/academic-records*') ? 'active' : '' }}">

                <i class="fa-solid fa-clipboard-list"></i>

                <span>Academic Records</span>

                </a>

                    <a href="{{ url('/adviser/records?subject=mathematics') }}" class="adviser-sub-link">
                        <i class="fa-solid fa-calculator"></i>
                        <span>Mathematics</span>
                    </a>

                    <a href="{{ url('/adviser/records?subject=english') }}" class="adviser-sub-link">
                        <i class="fa-solid fa-book-open"></i>
                        <span>English</span>
                    </a>

                    <a href="{{ url('/adviser/records?subject=science') }}" class="adviser-sub-link">
                        <i class="fa-solid fa-flask"></i>
                        <span>Science</span>
                    </a>

                    <a href="{{ url('/adviser/records?subject=filipino') }}" class="adviser-sub-link">
                        <i class="fa-solid fa-book"></i>
                        <span>Filipino</span>
                    </a>

                    <a href="{{ url('/adviser/records?subject=araling-panlipunan') }}" class="adviser-sub-link">
                        <i class="fa-solid fa-earth-asia"></i>
                        <span>Araling Panlipunan</span>
                    </a>

                    <a href="{{ url('/adviser/records?subject=mapeh') }}" class="adviser-sub-link">
                        <i class="fa-solid fa-music"></i>
                        <span>MAPEH</span>
                    </a>

                    <a href="{{ url('/adviser/records?subject=tle') }}" class="adviser-sub-link">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                        <span>TLE</span>
                    </a>

                    <a href="{{ url('/adviser/records?subject=values-education') }}" class="adviser-sub-link">
                        <i class="fa-solid fa-heart"></i>
                        <span>Values Education</span>
                    </a>

                </div>

            </div>

            {{-- GRADE SUBMISSION HISTORY --}}

            <a href="{{ url('/adviser/grade-review') }}"
                class="adviser-nav-link {{ request()->is('adviser/grade-review*') ? 'active' : '' }}">

                <i class="fa-solid fa-file-lines"></i>

                <span>Grade Submission History</span>

            </a>



            {{-- SYSTEM --}}

            <p class="adviser-nav-title">SYSTEM</p>

            {{-- ANNOUNCEMENTS --}}

            <a href="{{ url('/adviser/announcements') }}"
                class="adviser-nav-link {{ request()->is('adviser/announcements*') ? 'active' : '' }}">

                <i class="fa-solid fa-bullhorn"></i>

                <span>Announcements</span>

            </a>

            {{-- MY PROFILE --}}

            <a href="{{ url('/adviser/profile') }}"
                class="adviser-nav-link {{ request()->is('adviser/profile*') ? 'active' : '' }}">

                <i class="fa-solid fa-user"></i>

                <span>My Profile</span>

            </a>

            {{-- SETTINGS --}}
            <a href="{{ url('/adviser/settings') }}"
                class="adviser-nav-link {{ request()->is('adviser/settings*') ? 'active' : '' }}">

                <i class="fa-solid fa-gear"></i>

                <span>Settings</span>

            </a>

        </nav>

        {{-- LOGOUT --}}
        <div class="adviser-logout-section">

            <a href="{{ url('/login') }}" class="adviser-nav-link adviser-logout">

                <i class="fa-solid fa-right-from-bracket"></i>

                <span>Logout</span>

            </a>

        </div>

    </aside>

    {{-- MAIN CONTENT --}}
    <main class="adviser-main-content">

        {{-- TOP HEADER --}}
        <header class="adviser-top-header">

            {{-- HEADER TITLE --}}
            <div class="adviser-header-title">
                <h1>
                    @yield('header', 'My Dashboard')
                </h1>

                <p>
                    @yield('header-description', 'Welcome back, Adviser!')
                </p>

            </div>

            {{-- HEADER RIGHT --}}
            <div class="adviser-header-right">

                {{-- NOTIFICATIONS --}}
                <div class="notification-wrapper">

                    <button class="notification-btn" id="notificationBtn" type="button">

                        <i class="fa-regular fa-bell"></i>

                        <span class="notification-dot"></span>

                    </button>

                    {{-- NOTIFICATION PANEL --}}

                    <div class="notification-panel" id="notificationPanel">

                        <div class="notification-header">

                            <div>

                                <h3>Notifications</h3>

                                <span>0 unread</span>

                            </div>

                            <button class="notification-close" id="notificationClose" type="button">

                                <i class="fa-solid fa-xmark"></i>

                            </button>

                        </div>

                        {{-- EMPTY NOTIFICATIONS --}}

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

                {{-- ADVISER PROFILE --}}

                <div class="profile-wrapper">

                    <button class="profile" id="profileBtn" type="button">

                        <div class="profile-icon">

                            <i class="fa-solid fa-user-tie"></i>

                        </div>

                        <div class="profile-info">

                            <strong>Adviser</strong>

                            <span>Adviser Account</span>

                        </div>

                        <i class="fa-solid fa-chevron-down profile-arrow"></i>

                    </button>

                    {{-- PROFILE MENU --}}

                    <div class="profile-menu" id="profileMenu">

                        <div class="profile-menu-header">

                            <div class="profile-menu-icon">

                                <i class="fa-solid fa-user-tie"></i>

                            </div>

                            <div>

                                <strong>Adviser</strong>

                                <span>Adviser Account</span>

                            </div>

                        </div>

                        <div class="profile-menu-divider"></div>

                        {{-- MY PROFILE --}}

                        <a href="{{ url('/adviser/profile') }}" class="profile-menu-item">

                            <i class="fa-solid fa-user"></i>

                            <span>My Profile</span>

                        </a>

                        {{-- SETTINGS --}}

                        <a href="{{ url('/adviser/settings') }}" class="profile-menu-item">

                            <i class="fa-solid fa-gear"></i>

                            <span>Settings</span>

                        </a>

                        {{-- LOGOUT --}}

                        <button type="button" class="profile-menu-item logout-item" id="logoutBtn">

                            <i class="fa-solid fa-right-from-bracket"></i>

                            <span>Logout</span>

                        </button>

                    </div>

                </div>

            </div>

        </header>

        {{-- PAGE CONTENT --}}

        @yield('content')

    </main>

    {{-- SHARED JAVASCRIPT --}}

    <script src="{{ asset('js/notification.js') }}"></script>

    <script src="{{ asset('js/profile.js') }}"></script>

    <script src="{{ asset('js/adviser/adviser-sidebar.js') }}"></script>

    {{-- PAGE-SPECIFIC JAVASCRIPT --}}

    @yield('page-js')

</body>

</html>