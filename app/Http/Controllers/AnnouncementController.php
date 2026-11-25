<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\AnnouncementNotification;
use App\Services\ActivityRecorder;

class AnnouncementController extends Controller
{
    // DISPLAY ANNOUNCEMENTS
    public function index()
    {
        $announcements = Announcement::latest()->get();

        return view('admin.announcement', compact('announcements'));
    }

    // DELETE ANNOUNCEMENT
    public function destroy($id, ActivityRecorder $activityRecorder)
    {
        $announcement = Announcement::findOrFail($id);

        $activityRecorder->record(
            action: 'delete',
            type: 'announcement',
            key: (string) $announcement->id,
            before: [
                'title' => $announcement->title,
                'message' => $announcement->message,
                'audience' => $announcement->audience,
            ],
            details: [
                'description' => 'Deleted an announcement.',
            ]
        );

        $announcement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Announcement successfully deleted!',
        ]);
    }

    // SAVE ANNOUNCEMENT
    public function store(Request $request, ActivityRecorder $activityRecorder)
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

        // RECORD ANNOUNCEMENT ACTIVITY
        $activityRecorder->record(
            action: 'create',
            type: 'announcement',
            key: (string) $announcement->id,
            after: [
                'title' => $announcement->title,
                'message' => $announcement->message,
                'audience' => $announcement->audience,
            ],
            details: [
                'description' => 'Published an announcement.',
            ]
        );

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
    public function update(
        Request $request,
        $id,
        ActivityRecorder $activityRecorder
    ) {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'audience' => 'required|in:all,teachers,advisers,students',
        ]);

        $announcement = Announcement::findOrFail($id);

        // CAPTURE PREVIOUS VALUES
        $before = [
            'title' => $announcement->title,
            'message' => $announcement->message,
            'audience' => $announcement->audience,
        ];

        $announcement->update([
            'title' => $validated['title'],
            'message' => $validated['message'],
            'audience' => $validated['audience'],
        ]);

        // RECORD ANNOUNCEMENT UPDATE
        $activityRecorder->record(
            action: 'update',
            type: 'announcement',
            key: (string) $announcement->id,
            before: $before,
            after: [
                'title' => $announcement->title,
                'message' => $announcement->message,
                'audience' => $announcement->audience,
            ],
            details: [
                'description' => 'Updated an announcement.',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Announcement successfully updated!',
            'announcement' => $announcement,
        ]);
    }
}
