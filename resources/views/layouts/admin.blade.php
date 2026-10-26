<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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

    {{-- SIDEBAR --}}
    <aside class="sidebar">

        {{-- LOGO --}}
        <div class="logo-section">
            <div class="logo">
                <img src="{{ asset('images/forbesologo.png') }}" alt="Forbes Academy Logo">
            </div>

            <div class="school-name">
                <h2>Forbes Academy</h2>
                <span>Grading System</span>
            </div>
        </div>

        {{-- NAVIGATION --}}
        <nav class="navigation">

            <p class="nav-title">MAIN</p>

            {{-- DASHBOARD --}}
            <a href="{{ url('/admin/dashboard') }}"
                class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge"></i>
                <span>Dashboard</span>
            </a>

            {{-- USERS DROPDOWN --}}
            <div
                class="dropdown {{ request()->is('admin/users*') || request()->is('admin/admins*') || request()->is('admin/advisers*') || request()->is('admin/teachers*') || request()->is('admin/students*') || request()->is('admin/faculty-management*') ? 'active' : '' }}">

                <a href="#" class="nav-link dropdown-toggle">
                    <i class="fa-solid fa-users"></i>
                    <span>Users</span>
                    <i class="fa-solid fa-chevron-down dropdown-arrow"></i>
                </a>

                <div class="dropdown-menu">

                    <a href="/admin/users" class="dropdown-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users-gear"></i>
                        <span>User Management</span>
                    </a>

                    <a href="/admin/admins" class="dropdown-link {{ request()->is('admin/admins*') ? 'active' : '' }}">
                        <i class="fa-solid fa-user-shield"></i>
                        <span>Admin</span>
                    </a>

                    <a href="/admin/faculty-management"
                        class="dropdown-link {{ request()->is('admin/faculty-management*') ? 'active' : '' }}">
                        <i class="fa-solid fa-user-tie"></i>
                        <span>Faculty</span>
                    </a>

                    <a href="/admin/students"
                        class="dropdown-link {{ request()->is('admin/students*') ? 'active' : '' }}">
                        <i class="fa-solid fa-user-graduate"></i>
                        <span>Students</span>
                    </a>

                </div>
            </div>

            <p class="nav-title">ACADEMIC MANAGEMENT</p>

            {{-- SUBJECT MANAGEMENT --}}
            <a href="/admin/subjects" class="nav-link {{ request()->is('admin/subjects*') ? 'active' : '' }}">
                <i class="fa-solid fa-book"></i>
                <span>Subject Management</span>
            </a>

            {{-- GRADE LEVEL --}}
            <a href="/admin/grade-level" class="nav-link {{ request()->is('admin/grade-level*') ? 'active' : '' }}">
                <i class="fa-solid fa-school"></i>
                <span>Grade Level</span>
            </a>

            {{-- GRADE MANAGEMENT --}}
            <a href="/admin/grades" class="nav-link {{ request()->is('admin/grades*') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-column"></i>
                <span>Grade Management</span>
            </a>

            {{-- GRADE APPROVAL --}}
            <a href="/admin/grade-approval"
                class="nav-link {{ request()->is('admin/grade-approval*') ? 'active' : '' }}">
                <i class="fa-solid fa-check-double"></i>
                <span>Grade Approval</span>
            </a>

            <p class="nav-title">SYSTEM</p>

            {{-- MY PROFILE --}}
            <a href="/admin/profile" class="nav-link {{ request()->is('admin/profile*') ? 'active' : '' }}">
                <i class="fa-solid fa-user"></i>
                <span>My Profile</span>
            </a>

            {{-- ANNOUNCEMENTS --}}
            <a href="/admin/announcement" class="nav-link {{ request()->is('admin/announcement*') ? 'active' : '' }}">
                <i class="fa-solid fa-bullhorn"></i>
                <span>Announcements</span>
            </a>

            {{-- AUDIT LOGS --}}
            <a href="/admin/auditlogs" class="nav-link {{ request()->is('admin/auditlogs*') ? 'active' : '' }}">
                <i class="fa-solid fa-bars-progress"></i>
                <span>Audit Logs</span>
            </a>

            {{-- SETTINGS --}}
            <a href="/admin/settings" class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}">
                <i class="fa-solid fa-gear"></i>
                <span>Settings</span>
            </a>

        </nav>

        {{-- LOGOUT --}}
        <div class="logout-section">
            <a href="/login" class="nav-link logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>

    </aside>

    {{-- MAIN CONTENT --}}
    <main class="main-content">

        {{-- TOP HEADER --}}
        <header class="top-header">

            <div>
                <h1>@yield('header', 'My Dashboard')</h1>
            </div>

            <div class="header-right">

                {{-- NOTIFICATION --}}
                <div class="notification-wrapper">

                    <button class="notification-btn" id="notificationBtn" type="button" aria-label="Open notifications">

                        <i class="fa-regular fa-bell"></i>

                        {{-- Unread indicator --}}
                        <span class="notification-dot" id="notificationDot"
                            style="{{ ($unreadNotifications ?? collect())->isNotEmpty() ? '' : 'display: none;' }}">
                        </span>

                    </button>

                    {{-- NOTIFICATION PANEL --}}
                    <div class="notification-panel" id="notificationPanel">

                        {{-- Notification header --}}
                        <div class="notification-header">

                            <div>
                                <h3>Notifications</h3>

                                <span id="unreadNotificationCount">
                                    {{ ($unreadNotifications ?? collect())->count() }}
                                    unread
                                </span>
                            </div>

                            <button class="notification-close" id="notificationClose" type="button"
                                aria-label="Close notifications">
                                <i class="fa-solid fa-xmark"></i>
                            </button>

                        </div>

                        {{-- Notification list --}}
                        <div class="notification-list" id="notificationList">

                            @forelse (($notifications ?? collect()) as $notification)

                                                    <div class="notification-item {{ $notification->read_at ? '' : 'unread' }}"
                                                        data-notification-id="{{ $notification->id }}">

                                                        <div class="notification-item-icon">
                                                            <i class="fa-solid fa-bullhorn"></i>
                                                        </div>

                                                        <div class="notification-item-info">

                                                            <h4>
                                                                {{ $notification->data['title'] ?? 'New Announcement' }}
                                                            </h4>

                                                            <p>
                                                                {{ $notification->data['message'] ?? 'Admin posted a new announcement.' }}
                                                            </p>

                                                            <span class="notification-time">
                                                                {{ $notification->created_at
                                ->timezone(config('app.timezone'))
                                ->diffForHumans() }}
                                                            </span>

                                                            {{-- Notification actions --}}
                                                            <div class="notification-actions">

                                                                @if (!$notification->read_at)
                                                                    <button type="button" class="notification-action mark-read-btn"
                                                                        data-id="{{ $notification->id }}">
                                                                        <i class="fa-solid fa-check"></i>
                                                                        Mark as Read
                                                                    </button>
                                                                @else
                                                                    <span class="notification-read-label">
                                                                        <i class="fa-solid fa-check-double"></i>
                                                                        Read
                                                                    </span>
                                                                @endif

                                                                <button type="button" class="notification-action delete-notification-btn"
                                                                    data-id="{{ $notification->id }}">
                                                                    <i class="fa-regular fa-trash-can"></i>
                                                                    Delete
                                                                </button>

                                                            </div>

                                                        </div>

                                                    </div>

                            @empty

                                <div class="empty-notification" id="emptyNotification">

                                    <div class="empty-notification-icon">
                                        <i class="fa-regular fa-bell-slash"></i>
                                    </div>

                                    <h4>No notifications yet</h4>

                                    <p>You don't have any notifications at the moment.</p>

                                </div>

                            @endforelse

                        </div>

                    </div>

                </div>

                {{-- ADMIN PROFILE --}}
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

                    {{-- PROFILE MENU --}}
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

                        {{-- SETTINGS --}}
                        <a href="/admin/settings" class="profile-menu-item">
                            <i class="fa-solid fa-gear"></i>
                            <span>Settings</span>
                        </a>

                        {{-- MY PROFILE --}}
                        <a href="/admin/profile" class="profile-menu-item">
                            <i class="fa-solid fa-user"></i>
                            <span>My Profile</span>
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

    {{-- PAGE-SPECIFIC JAVASCRIPT --}}
    @yield('page-js')

</body>

</html>