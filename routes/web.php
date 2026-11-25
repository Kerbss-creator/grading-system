<?php

use Illuminate\Support\Facades\Route;

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
use App\Http\Controllers\AccountSetupController;

use App\Http\Controllers\SubjectController as AdminSubjectController;

use App\Http\Controllers\Admin\GradebookController;
use App\Http\Controllers\Admin\AdminGradeApprovalController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\SchoolYearController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\GradeLevelController;
use App\Http\Controllers\Admin\DatabaseBackupController;

use App\Http\Controllers\Adviser\AdviserClassController;
use App\Http\Controllers\Adviser\AdviserStudentController;
use App\Http\Controllers\Adviser\AdviserGradeReviewController;
use App\Http\Controllers\Adviser\AdviserGradeManagementController;
use App\Http\Controllers\Adviser\AdviserSubjectController;

use App\Http\Controllers\Teacher\GradeManagementController;
use App\Http\Controllers\Teacher\GradeSubmissionController;
use App\Http\Controllers\Teacher\GradeReviewController;
use App\Http\Controllers\Teacher\SubjectController;
use App\Http\Controllers\Admin\GradePublicationController;

use App\Http\Controllers\Student\StudentGradesController;
use App\Http\Controllers\Student\StudentSubjectsController;
use App\Models\Announcement;


/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES
|--------------------------------------------------------------------------
*/

// Account Password Setup
Route::get('/account/setup/{token}', [AccountSetupController::class, 'show'])
    ->name('account.setup.show');

Route::post('/account/setup/{token}', [AccountSetupController::class, 'store'])
    ->name('account.setup.store');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('password.request');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| AUTHENTICATED SHARED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

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
        Route::get('/users', [UserManagementController::class, 'index'])
            ->name('users.index');

        Route::get('/users/data', [UserManagementController::class, 'getUsers'])
            ->name('users.data');

        Route::post('/users', [UserManagementController::class, 'store'])
            ->name('users.store');

        Route::put('/users/{user}', [UserManagementController::class, 'update'])
            ->name('users.update');

        Route::patch(
            '/users/{user}/status',
            [UserManagementController::class, 'toggleStatus']
        )->name('users.toggle-status');

        Route::patch(
            '/users/{user}/password',
            [UserManagementController::class, 'resetPassword']
        )->name('users.reset-password');



        // Student Management
        Route::get('/students', [StudentController::class, 'index'])
            ->name('students.index');

        Route::post('/students', [StudentController::class, 'store'])
            ->name('students.store');

        // BULK ACTIONS
        Route::patch(
            '/students/bulk-status',
            [StudentController::class, 'bulkUpdateStatus']
        )->name('students.bulkStatus');

        Route::delete(
            '/students/bulk-delete',
            [StudentController::class, 'bulkDestroy']
        )->name('students.bulkDestroy');

        // INDIVIDUAL ACTIONS
        Route::put('/students/{id}', [StudentController::class, 'update'])
            ->name('students.update');

        Route::delete('/students/{id}', [StudentController::class, 'destroy'])
            ->name('students.destroy');

        Route::patch(
            '/students/{id}/toggle-status',
            [StudentController::class, 'toggleStatus']
        )->name('students.toggleStatus');



        // Teacher Management
        Route::get('/teachers', [TeacherController::class, 'index'])
            ->name('teachers');


        // Faculty Management
        Route::get('/faculty-management', [FacultyController::class, 'index'])
            ->name('faculty-management');

        Route::post('/faculty-management', [FacultyController::class, 'store'])
            ->name('faculty.store');

        Route::put('/faculty-management/{id}', [FacultyController::class, 'update'])
            ->name('faculty.update');

        Route::delete('/faculty-management/{id}', [FacultyController::class, 'destroy'])
            ->name('faculty.destroy');

        Route::patch(
            '/faculty-management/{id}/toggle-status',
            [FacultyController::class, 'toggleStatus']
        )->name('faculty.toggleStatus');

        Route::patch(
            '/faculty-management/bulk-status',
            [FacultyController::class, 'bulkStatus']
        )->name('faculty.bulkStatus');


        // CSV Import and Export
        Route::get('/imports/{type}', [SchoolCsvController::class, 'index'])
            ->name('imports.index');

        Route::post('/imports/{type}/preview', [SchoolCsvController::class, 'preview'])
            ->name('imports.preview');

        Route::post('/imports/{type}/confirm', [SchoolCsvController::class, 'confirm'])
            ->name('imports.confirm');

        Route::get('/exports/{type}', [SchoolCsvController::class, 'export'])
            ->name('exports.csv');


        // Activity Log and Recycle Bin
        Route::get('/activity-log', [ActivityLogController::class, 'index'])
            ->name('activity-log');


        Route::get('/activity-log/{log}', [ActivityLogController::class, 'show'])
            ->name('activity-log.show');


        Route::get('/recycle-bin', [ActivityLogController::class, 'trash'])
            ->name('recycle-bin');

        Route::post(
            '/recycle-bin/{type}/{id}/restore',
            [ActivityLogController::class, 'restore']
        )->name('recycle-bin.restore');

        // Permanently delete one record
        Route::delete(
            '/recycle-bin/{type}/{id}/permanent',
            [ActivityLogController::class, 'forceDelete']
        )->name('recycle-bin.force-delete');

        // Permanently delete selected records
        Route::delete(
            '/recycle-bin/{type}/permanent',
            [ActivityLogController::class, 'forceDeleteSelected']
        )->name('recycle-bin.force-delete-selected');


        // Subject Management
        Route::get('/subjects', [AdminSubjectController::class, 'index'])
            ->name('subjects.index');

        Route::get('/subject-details', [AdminSubjectController::class, 'show'])
            ->name('subject-details');


        // Grade Level Management
        Route::get('/grade-level', [GradeLevelController::class, 'index'])
            ->name('grade-level');

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


        // Grade Management
        Route::get('/grades', function () {
            return view('admin.grade-management');
        })->name('grades.index');


        // Gradebook
        Route::get('/gradebook', [GradebookController::class, 'index'])
            ->name('gradebook');


        // Grade Approval
        Route::get(
            '/grade-approval',
            [AdminGradeApprovalController::class, 'index']
        )->name('grade-approval');

        Route::post(
            '/grade-approval/{assignmentId}/{schoolYear}/{term}/approve',
            [AdminGradeApprovalController::class, 'approve']
        )->whereNumber('assignmentId')
            ->whereNumber('term')
            ->name('grade-approval.approve');

        Route::post(
            '/grade-approval/{assignmentId}/{schoolYear}/{term}/return',
            [AdminGradeApprovalController::class, 'returnToTeacher']
        )->whereNumber('assignmentId')
            ->whereNumber('term')
            ->name('grade-approval.return');

        Route::get(
            '/grade-approval/{assignmentId}/{schoolYear}/{term}',
            [AdminGradeApprovalController::class, 'show']
        )->whereNumber('assignmentId')
            ->whereNumber('term')
            ->name('grade-approval.show');

        Route::get('/grade-publications', [GradePublicationController::class, 'index'])
            ->name('grade-publications.index');

        Route::post('/grade-publications/{schoolYear}/{term}/publish', [GradePublicationController::class, 'publish'])
            ->name('grade-publications.publish');

        // Announcements
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

        // Admin Profile
        Route::get('/profile', [AdminProfileController::class, 'index'])
            ->name('profile');

        Route::put('/profile', [AdminProfileController::class, 'update'])
            ->name('profile.update');

        Route::put('/profile/password', [AdminProfileController::class, 'updatePassword'])
            ->name('profile.password');

        Route::post('/profile/photo', [AdminProfileController::class, 'updatePhoto'])
            ->name('profile.photo.update');

        Route::delete('/profile/photo', [AdminProfileController::class, 'removePhoto'])
            ->name('profile.photo.remove');


        // Settings
        Route::get('/settings', [SchoolYearController::class, 'index'])
            ->name('settings');

        // School Year Management
        Route::post(
            '/settings/school-years',
            [SchoolYearController::class, 'store']
        )->name('settings.school-years.store');

        // Update Upcoming School Year
        Route::patch(
            '/settings/school-years/{schoolYear}',
            [SchoolYearController::class, 'update']
        )->name('settings.school-years.update');

        Route::patch(
            '/settings/school-years/{schoolYear}/activate',
            [SchoolYearController::class, 'activate']
        )->name('settings.school-years.activate');

        Route::patch(
            '/settings/school-years/{schoolYear}/term',
            [SchoolYearController::class, 'updateTerm']
        )->name('settings.school-years.term');

        // Database Backup Management
        Route::get(
            '/settings/backups',
            [DatabaseBackupController::class, 'index']
        )->name('settings.backups.index');

        Route::post(
            '/settings/backups',
            [DatabaseBackupController::class, 'store']
        )->name('settings.backups.store');

        Route::get(
            '/settings/backups/{filename}/download',
            [DatabaseBackupController::class, 'download']
        )->where('filename', '[A-Za-z0-9_.-]+')
            ->name('settings.backups.download');
            
        Route::post(
            '/settings/backups/{filename}/restore',
            [DatabaseBackupController::class, 'restore']
        )->where('filename', '[A-Za-z0-9_.-]+')
            ->name('settings.backups.restore');




    });


/*
|--------------------------------------------------------------------------
| TEACHER ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'school.role:teacher,teacher_adviser'])
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

        Route::get('/grade-management/load', [GradeManagementController::class, 'load'])
            ->name('grade-management.load');

        Route::post('/grade-management/save', [GradeManagementController::class, 'save'])
            ->name('grade-management.save');

        Route::post(
            '/grade-management/assessment-settings',
            [GradeManagementController::class, 'updateAssessmentSettings']
        )->name('grade-management.assessment-settings');

        Route::post(
            '/grade-management/reset',
            [GradeManagementController::class, 'reset']
        )->name('grade-management.reset');

        Route::post(
            '/grade-management/clear-scores',
            [GradeManagementController::class, 'clearScores']
        )->name('grade-management.clear-scores');


        // Grade Submission
        Route::get(
            '/grade-submission',
            [GradeSubmissionController::class, 'index']
        )->name('grade-submission');

        Route::post(
            '/grade-submission/submit',
            [GradeSubmissionController::class, 'submit']
        )->name('grade-submission.submit');


        // Teacher Grade Review
        Route::get(
            '/grade-review',
            [GradeReviewController::class, 'index']
        )->name('grade-review');


        // My Subjects
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

Route::middleware(['auth', 'school.role:adviser,teacher_adviser'])
    ->prefix('adviser')
    ->name('adviser.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'adviser'])
            ->name('dashboard');


        // Advisory Classes
        Route::get(
            '/classes',
            [AdviserClassController::class, 'index']
        )->name('classes');

        // My Subjects
        Route::get(
            '/subjects',
            [AdviserSubjectController::class, 'index']
        )->name('subjects');

        // Adviser Grade Management
        Route::get(
            '/grade-management',
            [AdviserGradeManagementController::class, 'index']
        )->name('grade-management');

        // Load Assessment Scores
        Route::get(
            '/grade-management/load',
            [AdviserGradeManagementController::class, 'load']
        )->name('grade-management.load');

        // Save Assessment Scores
        Route::post(
            '/grade-management/save',
            [AdviserGradeManagementController::class, 'save']
        )->name('grade-management.save');

        // Update Assessment Settings
        Route::post(
            '/grade-management/assessment-settings',
            [AdviserGradeManagementController::class, 'updateAssessmentSettings']
        )->name('grade-management.assessment-settings');

        // Reset Assessment Settings
        Route::post(
            '/grade-management/reset',
            [AdviserGradeManagementController::class, 'reset']
        )->name('grade-management.reset');

        // Clear Assessment Scores
        Route::post(
            '/grade-management/clear-scores',
            [AdviserGradeManagementController::class, 'clearScores']
        )->name('grade-management.clear-scores');

        // Submit Grades
        Route::post(
            '/grade-management/submit',
            [AdviserGradeManagementController::class, 'submit']
        )->name('grade-management.submit');


        // My Students
        Route::get(
            '/students',
            [AdviserStudentController::class, 'index']
        )->name('students');




        // Academic Records
        Route::get('/academic-records', function () {
            return view('adviser.academic-records');
        })->name('academic-records');


        // Adviser Grade Review
        Route::get(
            '/grade-review',
            [AdviserGradeReviewController::class, 'index']
        )->name('grade-review');

        Route::get(
            '/grade-review/{assignmentId}/{schoolYear}/{term}',
            [AdviserGradeReviewController::class, 'show']
        )->whereNumber('assignmentId')
            ->whereNumber('term')
            ->name('grade-review.show');

        Route::post(
            '/grade-review/{assignmentId}/{schoolYear}/{term}/forward',
            [AdviserGradeReviewController::class, 'forward']
        )->whereNumber('assignmentId')
            ->whereNumber('term')
            ->name('grade-review.forward');

        Route::post(
            '/grade-review/{assignmentId}/{schoolYear}/{term}/return',
            [AdviserGradeReviewController::class, 'returnToTeacher']
        )->whereNumber('assignmentId')
            ->whereNumber('term')
            ->name('grade-review.return');


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
        Route::get('/grades', [StudentGradesController::class, 'index'])
            ->name('grades');

        // My Subjects
        Route::get('/subjects', [StudentSubjectsController::class, 'index'])
            ->name('subjects');


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
