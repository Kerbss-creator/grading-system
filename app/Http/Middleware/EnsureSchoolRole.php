<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolRole
{
    public function handle(
        Request $request,
        Closure $next,
        string $role
    ): Response {

        $user = $request->user();

        // Check kung authenticated ang user
        abort_unless($user, 401);

        // Check kung tugma ang role
        abort_unless($user->role === $role, 403);

        return $next($request);
    }
}
