<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // Mark one notification as read
    public function markAsRead(string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $notification = $user
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    // Delete one notification belonging to the logged-in user
    public function destroy(string $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $notification = $user
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted.',
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }
}
