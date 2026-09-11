
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Forbes Academy Grading System')</title>

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Global CSS -->
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">

    <!-- Sidebar CSS -->
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">

    <!-- Page CSS -->
    @yield('page-css')
</head>

<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <!-- LOGO -->
        <div class="logo-section">

            <div class="logo">
                <img src="{{ asset('images/forbesologo.png') }}" alt="Forbes Academy Logo">
            </div>

            <div class="school-name">
                <h2>Forbes Academy</h2>
                <span>Grading System</span>
            </div>

        </div>


        <!-- NAVIGATION -->
        <nav class="navigation">

            <p class="nav-title">MAIN</p>


            <!-- DASHBOARD -->
            <a href="/admin/dashboard" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">

                <i class="fa-solid fa-gauge"></i>

                <span>Dashboard</span>

            </a>


            <!-- USER MANAGEMENT -->
            <div
                class="dropdown {{ request()->is('admin/students*') || request()->is('admin/teachers*') ? 'active' : '' }}">

                
                    <a href="/admin/users"
                        class="nav-link dropdown
                    {{ request()->is('admin/users*') || request()->is('admin/students*') || request()->is('admin/teachers*') ? 'active' : '' }}">

                        <i class="fa-solid fa-users"></i>

                        <span>User Management</span>

                        <i class="fa-solid fa-chevron-down dropdown-arrow"></i>

                    </a>
                


                <!-- USER MANAGEMENT SUBMENU -->
                <div class="dropdown-menu">

                    <!-- STUDENT MANAGEMENT -->
                    <a href="/admin/students"
                        class="dropdown-link {{ request()->is('admin/students*') ? 'active' : '' }}">

                        <i class="fa-solid fa-user-graduate"></i>

                        <span>Student Management</span>

                    </a>


                    <!-- TEACHER MANAGEMENT -->
                    <a href="/admin/teachers"
                        class="dropdown-link {{ request()->is('admin/teachers*') ? 'active' : '' }}">

                        <i class="fa-solid fa-chalkboard-user"></i>

                        <span>Teacher Management</span>

                    </a>

                </div>

            </div>


            <!-- SUBJECT MANAGEMENT -->
            <a href="/admin/subjects" class="nav-link {{ request()->is('admin/subjects*') ? 'active' : '' }}">

                <i class="fa-solid fa-book"></i>

                <span>Subject Management</span>

            </a>


            <!-- GRADE LEVEL -->
            <a href="/admin/grade-level" class="nav-link {{ request()->is('admin/grade-level*') ? 'active' : '' }}">

                <i class="fa-solid fa-school"></i>

                <span>Grade Level</span>

            </a>


            <!-- GRADE MANAGEMENT -->
            <a href="/admin/grades" class="nav-link {{ request()->is('admin/grades*') ? 'active' : '' }}">

                <i class="fa-solid fa-chart-column"></i>

                <span>Grade Management</span>

            </a>


            <!-- GRADE APPROVAL -->
            <a href="/admin/grade-approval"
                class="nav-link {{ request()->is('admin/grade-approval*') ? 'active' : '' }}">

                <i class="fa-solid fa-check-double"></i>

                <span>Grade Approval</span>

            </a>


            <p class="nav-title">SYSTEM</p>


            <!-- ANNOUNCEMENTS -->
            <a href="/admin/announcement" class="nav-link {{ request()->is('admin/announcement*') ? 'active' : '' }}">

                <i class="fa-solid fa-bullhorn"></i>

                <span>Announcements</span>

            </a>


            <!-- SETTINGS -->
            <a href="/admin/settings" class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}">

                <i class="fa-solid fa-gear"></i>

                <span>Settings</span>

            </a>

        </nav>


        <!-- LOGOUT -->
        <div class="logout-section">

            <a href="/login" class="nav-link logout">

                <i class="fa-solid fa-right-from-bracket"></i>

                <span>Logout</span>

            </a>

        </div>

    </aside>


    <!-- MAIN CONTENT -->
    <main class="main-content">


        <!-- TOP HEADER -->
        <header class="top-header">

            <div>

                <h1>
                    @yield('header', 'Dashboard')
                </h1>

                <p>
                    @yield('header-description', 'Welcome back, Administrator!')
                </p>

            </div>


            <div class="header-right">


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

                                <h3>Notifications</h3>

                                <span>0 unread</span>

                            </div>


                            <button class="notification-close" id="notificationClose" type="button">

                                <i class="fa-solid fa-xmark"></i>

                            </button>

                        </div>


                        <div class="empty-notification">

                            <div class="empty-notification-icon">

                                <i class="fa-regular fa-bell-slash"></i>

                            </div>

                            <h4>No notifications yet</h4>

                            <p>
                                You don't have any notifications at the moment.
                            </p>

                        </div>

                    </div>

                </div>


                <!-- ADMIN PROFILE -->
                <div class="profile-wrapper">

                    <button class="profile" id="profileBtn" type="button">

                        <div class="profile-icon">

                            <i class="fa-solid fa-user"></i>

                        </div>


                        <div class="profile-info">

                            <strong>Administrator</strong>

                            <span>Admin</span>

                        </div>


                        <i class="fa-solid fa-chevron-down profile-arrow"></i>

                    </button>


                    <!-- PROFILE MENU -->
                    <div class="profile-menu" id="profileMenu">


                        <div class="profile-menu-header">

                            <div class="profile-menu-icon">

                                <i class="fa-solid fa-user"></i>

                            </div>


                            <div>

                                <strong>Administrator</strong>

                                <span>Admin Account</span>

                            </div>

                        </div>


                        <div class="profile-menu-divider"></div>


                        <!-- SETTINGS -->
                        <a href="/admin/settings" class="profile-menu-item">

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