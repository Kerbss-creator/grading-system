<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        abort_unless($request->user()?->role === $role && $request->user()->is_active, 403);
        $model = $role === 'student' ? \App\Models\Student::class : \App\Models\Teacher::class;
        $record = $model::where('user_id', $request->user()->id)->first()
            ?? $model::whereNull('user_id')->where('email', $request->user()->email)->first();
        abort_if($record && $record->status !== 'active', 403);
        return $next($request);
    }
}
