<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AnnouncementController;
use App\Models\Announcement;

// Route::get('/login', [AuthController::class,'login']);

// LOGIN
Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');

// RESET PASSWORD
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request');




// ADMIN ROUTE

// Dashboard
Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
    ->name('admin.dashboard');

// User Management
Route::get('/admin/users', function () {
    return view('admin.user-management');
});

// STUDENT MANAGEMENT

// student management 
Route::get('/admin/students', function () {
    $students = DB::table('students')->get();

    return view('admin.student-management', compact('students'));
});

// Save a new student
Route::post('/admin/students', [StudentController::class, 'store'])
    ->name('admin.students.store');
    

// Teacher Management
Route::get('/admin/teachers', [TeacherController::class, 'index'])
    ->name('admin.teachers');

    
// FACULTY MANAGEMENT
Route::get('/admin/faculty-management', function () {
    return view('admin.faculty-management');
})->name('admin.faculty-management');



// Admin Management
Route::get('/admin/admins', function () {
    return view('admin.admin-management');
})->name('admin.management');

// Subject Management
Route::get('/admin/subjects', function () {
    return view('admin.subject-management');
});

// Grade Level
Route::get('/admin/grade-level', function () {
    return view('admin.grade-level');
});

// Grade Management
Route::get('/admin/grades', function () {
    return view('admin.grade-management');
});

// Grade Approval
Route::get('/admin/grade-approval', function () {
    return view('admin.grade-approval');
});

// Announcements

// Admin Announcements
Route::get('/admin/announcement', [
    AnnouncementController::class,
    'index'
])->name('admin.announcement');

//POST ANNOUNCEMENT
Route::post('/admin/announcement', [
    AnnouncementController::class,
    'store'
])->name('admin.announcement.store');

// UPDATE ANNOUNCEMENT
Route::put('/admin/announcement/{id}', [
    AnnouncementController::class,
    'update'
])->name('admin.announcement.update');

//DELETE ANNOUNCEMENT
Route::delete('/admin/announcement/{id}', [
    AnnouncementController::class,
    'destroy'
])->name('admin.announcement.destroy');

// Settings
Route::get('/admin/settings', function () {
    return view('admin.settings');
});






// TEACHERS ROUTE

// Dashboard
Route::get('/teacher/dashboard', [DashboardController::class, 'teacher'])
    ->name('teacher.dashboard');

// Teacher Classes
Route::get('/teacher/classes', function () {
    return view('teacher.classes');
});

// Teacher Grade Management
Route::get('/teacher/grades', function () {
    return view('teacher.grade-management');
});

// Teacher Grade Submission
Route::get('/teacher/grade-submission', function () {
    return view('teacher.grade-submission');
});

// Teacher Subjects
Route::get('/teacher/subjects', function () {
    return view('teacher.subjects');
});

// Teacher Profile
Route::get('/teacher/profile', function () {
    return view('teacher.profile');
});

// Teacher Announcement
Route::get('/teacher/announcements', function () {
    $announcements = \App\Models\Announcement::whereIn('audience', [
        'all',
        'teachers',
    ])
        ->latest()
        ->get();

    return view('teacher.announcements', compact('announcements'));
})->name('teacher.announcements');


// Teacher Settings
Route::get('/teacher/settings', function () {
    return view('teacher.settings');
})->name('teacher.settings');

// ADVISER

Route::get('/adviser/dashboard', [DashboardController::class, 'adviser'])
    ->name('adviser.dashboard');

// ADVISER CLASSES PAGE
Route::get('adviser/classes', function () {
    return view('adviser.classes');
})->name('classes');

// MY STUDENTS
Route::get('adviser/students', function () {
    return view('adviser.students');
})->name('students');

// ACADEMIC RECORDS
Route::get('/adviser/academic-records', function () {
    return view('adviser.academic-records');
})->name('adviser.academic-records');


// GRADE SUBMISSION HISTORY
Route::get('/adviser/grade-review', function () {
    return view('adviser.grade-review');
})->name('adviser.grade-review');

// ANNOUNCEMENT
Route::get('/adviser/announcements', function () {
    $announcements = \App\Models\Announcement::whereIn('audience', [
        'all',
        'advisers',
    ])
        ->latest()
        ->get();

    return view('adviser.announcements', compact('announcements'));
})->name('adviser.announcements');

// ADVISER PROFILE
Route::get('/adviser/profile', function () {
    return view('adviser.profile');
});

// ADVISER SETTINGS
Route::get('/adviser/settings', function () {
    return view('adviser.settings');
});

// STUDENTS ROUTE

// STUDENT DASHBOARD
Route::get('/student/dashboard', [DashboardController::class, 'student'])
    ->name('student.dashboard');

// STUDENT GRADES ROUTE
Route::view('/student/grades', 'student.grades')
    ->name('student.grades');

// STUDENT ANNOUNCEMENT 

Route::get('/student/announcements', function () {
    $announcements = \App\Models\Announcement::whereIn('audience', [
        'all',
        'students',
    ])
        ->latest()
        ->get();

    return view('student.announcements', compact('announcements'));
})->name('student.announcements');


// STUDENT PROFILE 
Route::view('/student/profile', 'student.profile')
    ->name('student.profile');