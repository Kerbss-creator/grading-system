<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function admin()
    {
        return view('admin.dashboard-admin');
    }

    public function teacher()
    {
        return view('teacher.dashboard');
    }

    public function adviser()
    {
        return view('adviser.dashboard');
    }

    public function student()
    {
        return view('student.dashboard');
    }
}