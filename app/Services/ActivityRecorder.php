<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityRecorder
{
    public function record(string $action, string $type, ?string $key = null, ?array $before = null, ?array $after = null, array $details = []): void
    {
        $user = Auth::user();
        ActivityLog::create([
            'actor_id' => $user?->id,
            'actor_name' => $user?->name ?? 'System (CLI)',
            'actor_email' => $user?->email,
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
