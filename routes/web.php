<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\SchoolCsvController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\AcademicController;
use App\Http\Controllers\TeacherDashboardController;
use Illuminate\Http\Request;
use App\Http\Controllers\FacultyController;

Route::redirect('/', '/login');
Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::middleware(['auth', 'admin.role'])->prefix('admin')->group(function () {
    Route::redirect('/', '/admin/dashboard')->name('admin.home');
    Route::redirect('/dashbord', '/admin/dashboard');
    Route::redirect('/profile', '/admin/settings')->name('admin.profile');

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/users', [UserManagementController::class, 'index'])->name('admin.users');
    Route::get('/admins', [UserManagementController::class, 'adminsIndex'])->name('admin.admins.index');
    Route::post('/users/admins', [UserManagementController::class, 'storeAdmin'])->name('admin.users.admins.store');
    Route::put('/users/admins/{user}', [UserManagementController::class, 'updateAdmin'])->name('admin.users.admins.update');
    Route::post('/users/{user}/password', [UserManagementController::class, 'resetPassword'])->name('admin.users.password');
    Route::delete('/users/admins/bulk', [UserManagementController::class, 'bulkDelete'])->name('admin.users.admins.bulkDestroy');
    Route::patch('/users/admins/bulk-status', [UserManagementController::class, 'bulkStatusAdmins'])->name('admin.users.admins.bulkStatus');
    Route::delete('/users/bulk', [UserManagementController::class, 'bulkDeleteAccounts'])->name('admin.users.bulkDestroy');
    Route::get('/students', [StudentController::class, 'index'])->name('admin.students.index');
    Route::get('/students/{student}/academic', [AcademicController::class, 'student'])->name('admin.students.show');
    Route::post('/students', [StudentController::class, 'store'])->name('admin.students.store');
    Route::put('/students/{id}', [StudentController::class, 'update'])->name('admin.students.update');
    Route::delete('/students/bulk', [StudentController::class, 'bulkDestroy'])->name('admin.students.bulkDestroy');
    Route::patch('/students/bulk-status', [StudentController::class, 'bulkStatus'])->name('admin.students.bulkStatus');
    Route::delete('/students/{id}', [StudentController::class, 'destroy'])->name('admin.students.destroy');
    Route::patch('/students/{id}/toggle-status', [StudentController::class, 'toggleStatus'])->name('admin.students.toggleStatus');
    Route::get('/teachers', [TeacherController::class, 'index'])->name('admin.teachers.index');
    Route::get('/teachers/{teacher}/academic', [AcademicController::class, 'teacher'])->name('admin.teachers.show');
    Route::post('/teachers', [TeacherController::class, 'store'])->name('admin.teachers.store');
    Route::put('/teachers/{id}', [TeacherController::class, 'update'])->name('admin.teachers.update');
    Route::delete('/teachers/bulk', [TeacherController::class, 'bulkDestroy'])->name('admin.teachers.bulkDestroy');
    Route::patch('/teachers/bulk-status', [TeacherController::class, 'bulkStatus'])->name('admin.teachers.bulkStatus');
    Route::delete('/teachers/{id}', [TeacherController::class, 'destroy'])->name('admin.teachers.destroy');
    Route::patch('/teachers/{id}/toggle-status', [TeacherController::class, 'toggleStatus'])->name('admin.teachers.toggleStatus');
    Route::get('/imports/{type}', [SchoolCsvController::class, 'index'])->name('admin.imports.index');
    Route::post('/imports/{type}/preview', [SchoolCsvController::class, 'preview'])->name('admin.imports.preview');
    Route::post('/imports/{type}/confirm', [SchoolCsvController::class, 'confirm'])->name('admin.imports.confirm');
    Route::get('/exports/{type}', [SchoolCsvController::class, 'export'])->name('admin.exports.csv');
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('admin.activity-log');
    Route::get('/recycle-bin', [ActivityLogController::class, 'trash'])->name('admin.recycle-bin');
    Route::post('/recycle-bin/{type}/{id}/restore', [ActivityLogController::class, 'restore'])->name('admin.recycle-bin.restore');
    Route::get('/subjects', [AcademicController::class, 'subjects'])->name('admin.subjects');
    Route::post('/subjects', [AcademicController::class, 'storeSubject'])->name('admin.subjects.store');
    Route::get('/subjects/{subject}', [AcademicController::class, 'subject'])->name('admin.subjects.show');
    Route::patch('/subjects/{subject}', [AcademicController::class, 'updateSubject'])->name('admin.subjects.update');
    Route::post('/subjects/{subject}/offerings', [AcademicController::class, 'storeOffering'])->name('admin.offerings.store');
    Route::patch('/offerings/{offering}', [AcademicController::class, 'updateOffering'])->name('admin.offerings.update');
    Route::get('/grade-level', [AcademicController::class, 'gradeLevels'])->name('admin.grade-level');
    Route::post('/grade-level/adviser', [AcademicController::class, 'saveAdviser'])->name('admin.grade-level.adviser');
    Route::get('/grades', [AcademicController::class, 'grades'])->name('admin.grades');
    Route::post('/grades/{offering}', [AcademicController::class, 'saveGrade'])->name('admin.grades.save');
    Route::post('/grades/submit/selected', [AcademicController::class, 'submitGrades'])->name('admin.grades.submit');
    Route::get('/grade-approval', [AcademicController::class, 'approvals'])->name('admin.grade-approval');
    Route::post('/grade-approval/decision', [AcademicController::class, 'review'])->name('admin.grade-approval.review');
    Route::get('/announcement', function () { return view('admin.announcement'); })->name('admin.announcement');
    Route::get('/settings', function () { return view('admin.settings'); })->name('admin.settings');
});

Route::middleware(['auth','school.role:teacher'])->prefix('teacher')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');
    Route::get('/grades', [AcademicController::class, 'grades'])->name('teacher.grades');
    Route::post('/grades/{offering}', [AcademicController::class, 'saveGrade'])->name('teacher.grades.save');
    Route::post('/grades/submit/selected', [AcademicController::class, 'submitGrades'])->name('teacher.grades.submit');
    Route::get('/grade-approval', [AcademicController::class, 'approvals'])->name('teacher.grade-approval');
    Route::post('/grade-approval/decision', [AcademicController::class, 'review'])->name('teacher.grade-approval.review');
});

Route::middleware(['auth','school.role:student'])->prefix('student')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\StudentDashboardController::class, 'index'])->name('student.dashboard');
});


 // FACULTY MANAGEMENT
Route::get('/admin/faculty-management', [FacultyController::class, 'index'])
    ->name('admin.faculty-management');

Route::post('/admin/faculty-management', [FacultyController::class, 'store'])
    ->name('admin.faculty.store');

Route::put('/admin/faculty-management/{id}', [FacultyController::class, 'update'])
    ->name('admin.faculty.update');

Route::delete('/admin/faculty-management/{id}', [FacultyController::class, 'destroy'])
    ->name('admin.faculty.destroy');

// ================================================//


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



// Teacher Classes
Route::get('/teacher/classes', function () {
    return view('teacher.classes');
});

Route::get('/teacher/grade-management', function (Request $request) {
    $subjectCodes = [
        'MATH-7' => ['name' => 'Mathematics', 'grade' => 'Grade 7'],
        'SCI-8'  => ['name' => 'Science', 'grade' => 'Grade 8'],
        'ENG-9'  => ['name' => 'English', 'grade' => 'Grade 9'],
        'AP-10'  => ['name' => 'Araling Panlipunan', 'grade' => 'Grade 10'],
    ];

    $code = $request->query('subject', 'MATH-7');

    abort_unless(isset($subjectCodes[$code]), 404);

    return view('teacher.grade-management', [
        'subjectCode' => $code,
        'subjectName' => $subjectCodes[$code]['name'],
        'gradeLevel' => $subjectCodes[$code]['grade'],
        'schoolYear' => $request->query('school_year', '2026-2027'),
    ]);
})->name('teacher.grade-management');

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


// STUDENTS ROUTE

// STUDENT DASHBOARD

Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])
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