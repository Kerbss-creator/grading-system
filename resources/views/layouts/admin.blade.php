<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Forbes Academy Grading System')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/custom-modal.css') }}?v=10">

    <link rel="stylesheet" href="{{ asset('css/global.css') }}">

    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">

    @yield('page-css')
</head>

<body>

    <aside class="sidebar">

        <div class="logo-section">
            <div class="logo">
                <img src="{{ asset('images/forbesologo.png') }}" alt="Forbes Academy Logo">
            </div>
            <div class="school-name">
                <h2>Forbes Academy</h2>
                <span>Grading System</span>
            </div>
        </div>

        <nav class="navigation">
            <p class="nav-title">MAIN</p>

            <a href="{{ url('/admin/dashboard') }}" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge"></i>
                <span>Dashboard</span>
            </a>

            <div class="dropdown {{ request()->is('admin/users*') || request()->is('admin/admins*') || request()->is('admin/advisers*') || request()->is('admin/teachers*') || request()->is('admin/students*') || request()->is('admin/faculty-management*') ? 'active' : '' }}">
                <a href="#" class="nav-link dropdown-toggle{{ request()->is('admin/users*') || request()->is('admin/students*') || request()->is('admin/teachers*') ? 'active' : '' }}">
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

            <a href="/admin/subjects" class="nav-link {{ request()->is('admin/subjects*') ? 'active' : '' }}">
                <i class="fa-solid fa-book"></i>
                <span>Subject Management</span>
            </a>

            <a href="/admin/grade-level" class="nav-link {{ request()->is('admin/grade-level*') ? 'active' : '' }}">
                <i class="fa-solid fa-school"></i>
                <span>Grade Level</span>
            </a>

            <a href="/admin/grade-approval" class="nav-link {{ request()->is('admin/grade-approval*') ? 'active' : '' }}">
                <i class="fa-solid fa-check-double"></i>
                <span>Grade Approval</span>
            </a>

            <p class="nav-title">SYSTEM</p>

            <a href="{{ route('admin.profile') }}" class="nav-link {{ request()->is('admin/profile*') ? 'active' : '' }}">
                <i class="fa-solid fa-user"></i>
                <span>My Profile</span>
            </a>

            <a href="/admin/announcement" class="nav-link {{ request()->is('admin/announcement*') ? 'active' : '' }}">
                <i class="fa-solid fa-bullhorn"></i>
                <span>Announcements</span>
            </a>

            <a href="{{ route('admin.activity-log') }}" class="nav-link {{ request()->is('admin/activity-log') ? 'active' : '' }}">
                <i class="fa-solid fa-clock-rotate-left"></i><span>Activity Log</span>
            </a>
            <a href="{{ route('admin.recycle-bin') }}" class="nav-link {{ request()->is('admin/recycle-bin') ? 'active' : '' }}">
                <i class="fa-solid fa-trash-arrow-up"></i><span>Recycle Bin</span>
            </a>

            <a href="/admin/settings" class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}">
                <i class="fa-solid fa-gear"></i>
                <span>Settings</span>
            </a>
        </nav>

        <div class="logout-section">
            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="nav-link logout" style="background: none; border: none; width: 100%; text-align: left; cursor: pointer; color: inherit; font-family: inherit; display: flex; align-items: center; gap: 14px; padding: 13px 15px; margin: 4px 0; border-radius: 10px; font-size: 13px; font-weight: 500; white-space: nowrap;">
                    <i class="fa-solid fa-right-from-bracket" style="min-width: 20px; text-align: center; font-size: 15px;"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="main-content">

        <header class="top-header">
            <div>
                <h1>@yield('header', 'Dashboard')</h1>
                <p>@yield('header-description', 'Welcome back, ' . e(auth()->user()?->name ?? 'Administrator') . '!')</p>
            </div>

            <div class="header-right">

                <div class="notification-wrapper">
                    <button class="notification-btn" id="notificationBtn" type="button">
                        <i class="fa-regular fa-bell"></i>
                        <span class="notification-dot"></span>
                    </button>

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
                            <p>You don't have any notifications at the moment.</p>
                        </div>
                    </div>
                </div>

                <div class="profile-wrapper">
                    <button class="profile" id="profileBtn" type="button">
                        <div class="profile-icon">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <div class="profile-info">
                            <strong>{{ auth()->user()?->name ?? 'Administrator' }}</strong>
                            <span>Admin</span>
                        </div>
                        <i class="fa-solid fa-chevron-down profile-arrow"></i>
                    </button>

                    <div class="profile-menu" id="profileMenu">
                        <div class="profile-menu-header">
                            <div class="profile-menu-icon">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <strong>{{ auth()->user()?->name ?? 'Administrator' }}</strong>
                                <span>Admin Account</span>
                            </div>
                        </div>
                        <div class="profile-menu-divider"></div>

                        <a href="/admin/settings" class="profile-menu-item">
                            <i class="fa-solid fa-gear"></i>
                            <span>Settings</span>
                        </a>

                        <a href="{{ route('admin.profile') }}" class="profile-menu-item">
                            <i class="fa-solid fa-user"></i>
                            <span>My Profile</span>
                        </a>

                        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="profile-menu-item logout-item" style="width: 100%; text-align: left; background: none; border: none; cursor: pointer; font-family: inherit; display: flex; align-items: center; gap: 10px; padding: 11px 15px;">
                                <i class="fa-solid fa-right-from-bracket" style="width: 16px; text-align: center; font-size: 12px;"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        @yield('content')
    </main>

    <script src="{{ asset('js/notification.js') }}">
</script>
    <script src="{{ asset('js/profile.js') }}">
</script>

    @if(session('success'))<div data-flash-success hidden>{{ session('success') }}</div>@endif
    @if(session('error'))<div data-flash-error hidden>{{ session('error') }}</div>@endif
    @if($errors->any())<div data-flash-error hidden>{{ implode(PHP_EOL, $errors->all()) }}</div>@endif
    <script src="{{ asset('js/custom-modal.js') }}?v=11"></script>
    @yield('page-js')
</body>
</html>
