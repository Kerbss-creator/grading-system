<?php

use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/admin/dashboard', function () {
    return view('admin.dashboard-admin');
});

// User Management
Route::get('/admin/users', function () {
    return view('admin.user-management');
});

// Student Management
Route::get('/admin/students', function () {
    return view('admin.student-management');
});

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

// Login
Route::get('/login', function () {
    return view('auth.login');
});