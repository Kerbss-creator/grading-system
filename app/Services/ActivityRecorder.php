<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivityRecorder
{
    public function record(
        string $action,
        string $type,
        ?string $key = null,
        ?array $before = null,
        ?array $after = null,
        array $details = []
    ): void {
        $user = Auth::user();

        $actorId = $user?->getAuthIdentifier();

        // Get role from the authenticated user first.
        $actorRole = $user?->getAttribute('role');

        // Fallback: retrieve the actual role directly from the database.
        if ($actorId && empty($actorRole)) {
            $actorRole = DB::table('users')
                ->where('id', $actorId)
                ->value('role');
        }

        ActivityLog::create([
            'actor_id' => $actorId,
            'actor_name' => $user?->name ?? 'System (CLI)',
            'actor_email' => $user?->email,
            'actor_role' => $actorRole,
            'action' => $action,
            'record_type' => $type,
            'record_key' => $key,
            'before' => $before,
            'after' => $after,
            'details' => $details ?: null,
            'ip_address' => request()?->ip(),
            'created_at' => now(),
        ]);
    }
}
