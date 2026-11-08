<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureSchoolRole
{
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {

        $user = $request->user();

        // Check if user is logged in
        abort_unless($user, 401);

        // Check if faculty account is still active
        if (in_array($user->role, ['teacher', 'adviser', 'teacher_adviser'], true)) {

            $faculty = $user->faculty;

            if (!$faculty || $faculty->status !== 'active') {

                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->withErrors([
                        'email' => 'Your faculty account has been deactivated. Please contact the school administrator.'
                    ]);
            }
        }

        // Check if user's role is allowed
        abort_unless(
            in_array($user->role, $roles, true),
            403
        );

        return $next($request);
    }
}
