<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AnnouncementNotification;

class AnnouncementController extends Controller
{

    // DISPLAY ANNOUNCEMENT
    public function index()
    {
        $announcements = Announcement::latest()->get();

        return view('admin.announcement', compact('announcements'));
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);

        $announcement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Announcement successfully deleted!',
        ]);
    }

    // SAVE ANNOUNCEMENT

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'audience' => 'required|in:all,teachers,advisers,students',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'You must be logged in to publish an announcement.',
            ], 401);
        }

        $announcement = Announcement::create([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'audience' => $validated['audience'],
            'created_by' => Auth::id(),
        ]);

        // SEND NOTIFICATION TO ADMIN ACCOUNTS
        // TEMPORARY TEST: Notify the currently logged-in user
        // SEND NOTIFICATION TO THE CURRENTLY LOGGED-IN USER
        $user->notify(
            new AnnouncementNotification(
                $announcement->title,
                $announcement->message
            )
        );

        return response()->json([
            'success' => true,
            'message' => 'Announcement successfully added!',
            'announcement' => $announcement,
        ], 201);
    }

    // UPDATE ANNOUNCEMENT
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'audience' => 'required|in:all,teachers,advisers,students',
        ]);

        $announcement = Announcement::findOrFail($id);

        $announcement->update([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'audience' => $validated['audience'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Announcement successfully updated!',
            'announcement' => $announcement,
        ]);
    }
}