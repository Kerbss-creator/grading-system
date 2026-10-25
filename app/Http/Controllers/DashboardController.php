<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{


    public function admin()
    {
        // Get the 3 latest announcements for the dashboard
        $latestAnnouncements = \App\Models\Announcement::latest()
            ->take(3)
            ->get();

        // Get all announcements for the View All modal
        $allAnnouncements = \App\Models\Announcement::latest()
            ->get();

        return view('admin.dashboard-admin', compact(
            'latestAnnouncements',
            'allAnnouncements'
        ));
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