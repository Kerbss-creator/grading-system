<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\SchoolCsvController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Teacher\GradeManagementController;
use App\Http\Controllers\Teacher\SubjectController;
use App\Models\Announcement;


/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES
|--------------------------------------------------------------------------
*/

// Login
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit');

// Forgot Password
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| AUTHENTICATED SHARED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Notifications
    Route::patch(
        '/notifications/{id}/read',
        [NotificationController::class, 'markAsRead']
    )->name('notifications.read');

    Route::delete(
        '/notifications/{id}',
        [NotificationController::class, 'destroy']
    )->name('notifications.destroy');
});


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'school.role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get(
            '/dashboard',
            [AdminDashboardController::class, 'index']
        )->name('dashboard');


        // User Management
        Route::get('/users', function () {
            return view('admin.user-management');
        })->name('users.index');


        // Admin Management
        Route::get('/admins', function () {
            return view('admin.admin-management');
        })->name('management');


        /*
        |--------------------------------------------------------------------------
        | STUDENT MANAGEMENT
        |--------------------------------------------------------------------------
        */

        Route::get('/students', [StudentController::class, 'index'])
            ->name('students.index');

        Route::post('/students', [StudentController::class, 'store'])
            ->name('students.store');

        Route::put('/students/{id}', [StudentController::class, 'update'])
            ->name('students.update');

        Route::delete('/students/{id}', [StudentController::class, 'destroy'])
            ->name('students.destroy');

        Route::patch(
            '/students/{id}/toggle-status',
            [StudentController::class, 'toggleStatus']
        )->name('students.toggleStatus');



        /*
        |--------------------------------------------------------------------------
        | TEACHER MANAGEMENT
        |--------------------------------------------------------------------------
        */

        Route::get('/teachers', [TeacherController::class, 'index'])
            ->name('teachers');


        // FACULTY MANAGEMENT

        Route::get('/faculty-management', [FacultyController::class, 'index'])
            ->name('faculty-management');

        Route::post('/faculty-management', [FacultyController::class, 'store'])
            ->name('faculty.store');

        Route::put('/faculty-management/{id}', [FacultyController::class, 'update'])
            ->name('faculty.update');

        Route::delete('/faculty-management/{id}', [FacultyController::class, 'destroy'])
            ->name('faculty.destroy');

        Route::patch('/faculty-management/{id}/toggle-status', [FacultyController::class, 'toggleStatus'])
            ->name('faculty.toggleStatus');
            

        //csv import/export routes

        Route::get('/imports/{type}', [SchoolCsvController::class, 'index'])
            ->name('imports.index');

        Route::post('/imports/{type}/preview', [SchoolCsvController::class, 'preview'])
            ->name('imports.preview');

        Route::post('/imports/{type}/confirm', [SchoolCsvController::class, 'confirm'])
            ->name('imports.confirm');

        Route::get('/exports/{type}', [SchoolCsvController::class, 'export'])
            ->name('exports.csv');

        Route::get('/activity-log', [ActivityLogController::class, 'index'])
            ->name('activity-log');

        Route::get('/recycle-bin', [ActivityLogController::class, 'trash'])
            ->name('recycle-bin');

        Route::post('/recycle-bin/{type}/{id}/restore', [ActivityLogController::class, 'restore'])
            ->name('recycle-bin.restore');

        /*
        |--------------------------------------------------------------------------
        | SUBJECT MANAGEMENT
        |--------------------------------------------------------------------------
        */

        Route::get('/subjects', function () {
            return view('admin.subject-management');
        })->name('subjects.index');

        Route::get('/subject-details', function () {
            return view('admin.subject-details');
        })->name('subject-details');


        /*
        |--------------------------------------------------------------------------
        | GRADE LEVEL MANAGEMENT
        |--------------------------------------------------------------------------
        */

        Route::get('/grade-level', function () {
            return view('admin.grade-level');
        })->name('grade-level');

        Route::get('/grade-level/{grade}', function (int $grade) {

            $studentCounts = [
                7 => 168,
                8 => 167,
                9 => 166,
                10 => 167,
            ];

            abort_unless(
                array_key_exists($grade, $studentCounts),
                404
            );

            return view('admin.grade-level-details', [
                'gradeLevel' => 'Grade ' . $grade,
                'studentCount' => $studentCounts[$grade],
            ]);
        })->whereNumber('grade')->name('grade-level.details');


        /*
        |--------------------------------------------------------------------------
        | GRADE MANAGEMENT
        |--------------------------------------------------------------------------
        */

        Route::get('/grades', function () {
            return view('admin.grade-management');
        })->name('grades.index');

        Route::get('/grade-approval', function () {
            return view('admin.grade-approval');
        })->name('grade-approval');


        /*
        |--------------------------------------------------------------------------
        | ANNOUNCEMENTS
        |--------------------------------------------------------------------------
        */

        Route::get('/announcement', [
            AnnouncementController::class,
            'index'
        ])->name('announcement');

        Route::post('/announcement', [
            AnnouncementController::class,
            'store'
        ])->name('announcement.store');

        Route::put('/announcement/{id}', [
            AnnouncementController::class,
            'update'
        ])->name('announcement.update');

        Route::delete('/announcement/{id}', [
            AnnouncementController::class,
            'destroy'
        ])->name('announcement.destroy');


        /*
        |--------------------------------------------------------------------------
        | SETTINGS
        |--------------------------------------------------------------------------
        */

        Route::get('/settings', function () {
            return view('admin.settings');
        })->name('settings');
    });


/*
|--------------------------------------------------------------------------
| TEACHER ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'school.role:teacher'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'teacher'])
            ->name('dashboard');


        // My Classes
        Route::get('/classes', function () {
            return view('teacher.classes');
        })->name('classes');


        // Grade Management
        Route::get('/grade-management', [GradeManagementController::class, 'index'])
            ->name('grade-management');


        // Grade Submission
        Route::get('/grade-submission', function () {
            return view('teacher.grade-submission');
        })->name('grade-submission');


        // MY SUBJECTS
        Route::get('/subjects', [SubjectController::class, 'index'])
            ->name('subjects');


        // Announcements
        Route::get('/announcements', function () {

            $announcements = Announcement::whereIn('audience', [
                'all',
                'teachers',
            ])->latest()->get();

            return view('teacher.announcements', compact('announcements'));
        })->name('announcements');


        // My Profile
        Route::get('/profile', function () {
            return view('teacher.profile');
        })->name('profile');


        // Settings
        Route::get('/settings', function () {
            return view('teacher.settings');
        })->name('settings');
    });


/*
|--------------------------------------------------------------------------
| ADVISER ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'school.role:adviser'])
    ->prefix('adviser')
    ->name('adviser.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'adviser'])
            ->name('dashboard');


        // Advisory Classes
        Route::get('/classes', function () {
            return view('adviser.classes');
        })->name('classes');


        // My Students
        Route::get('/students', function () {
            return view('adviser.students');
        })->name('students');


        // Academic Records
        Route::get('/academic-records', function () {
            return view('adviser.academic-records');
        })->name('academic-records');


        // Grade Submission History
        Route::get('/grade-review', function () {
            return view('adviser.grade-review');
        })->name('grade-review');


        // Announcements
        Route::get('/announcements', function () {

            $announcements = Announcement::whereIn('audience', [
                'all',
                'advisers',
            ])->latest()->get();

            return view('adviser.announcements', compact('announcements'));
        })->name('announcements');


        // My Profile
        Route::get('/profile', function () {
            return view('adviser.profile');
        })->name('profile');


        // Settings
        Route::get('/settings', function () {
            return view('adviser.settings');
        })->name('settings');
    });


/*
|--------------------------------------------------------------------------
| STUDENT ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'school.role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [
            StudentDashboardController::class,
            'index'
        ])->name('dashboard');


        // Grades
        Route::view('/grades', 'student.grades')
            ->name('grades');


        // Announcements
        Route::get('/announcements', function () {

            $announcements = Announcement::whereIn('audience', [
                'all',
                'students',
            ])->latest()->get();

            return view('student.announcements', compact('announcements'));
        })->name('announcements');


        // My Profile
        Route::view('/profile', 'student.profile')
            ->name('profile');
    });
