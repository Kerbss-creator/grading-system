<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;

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

// Student Management
Route::get('/admin/students', [StudentController::class, 'index'])
    ->name('admin.students');

// Teacher Management
Route::get('/admin/teachers', function () {
    return view('admin.teacher-management');
});

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
Route::get('/admin/announcement', function () {
    return view('admin.announcement');
});

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
    return view('teacher.announcements');
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
    return view('adviser.announcements');
})->name('teacher.announcements');

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
Route::view('/student/announcements', 'student.announcements')
    ->name('student.announcements');

// STUDENT PROFILE 
Route::view('/student/profile', 'student.profile')
    ->name('student.profile');