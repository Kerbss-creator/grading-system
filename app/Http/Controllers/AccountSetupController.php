<?php

namespace App\Http\Controllers;

use App\Models\AccountSetupToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AccountSetupController extends Controller
{
    /**
     * Display the Set Password page.
     */
    public function show(string $token)
    {
        $hashedToken = hash('sha256', $token);

        $setupToken = AccountSetupToken::where('token', $hashedToken)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$setupToken) {
            return view('auth.account-setup-invalid');
        }

        return view('auth.set-password', [
            'token' => $token,
        ]);
    }

    /**
     * Save the user's password.
     */
    public function store(Request $request, string $token)
    {
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $hashedToken = hash('sha256', $token);

        $result = DB::transaction(function () use ($hashedToken, $request) {
            $setupToken = AccountSetupToken::where('token', $hashedToken)
                ->lockForUpdate()
                ->first();

            if (
                !$setupToken ||
                $setupToken->isUsed() ||
                $setupToken->isExpired()
            ) {
                return false;
            }

            $user = $setupToken->user;

            if (!$user || !$user->isAccountPending()) {
                return false;
            }

            $user->update([
                'password' => Hash::make($request->password),
                'account_status' => 'active',
            ]);

            $user->faculty()
                ->where('status', 'pending')
                ->update([
                    'status' => 'active',
                ]);

            $setupToken->update([
                'used_at' => now(),
            ]);

            return true;
        });

        if (!$result) {
            return view('auth.account-setup-invalid');
        }

        return redirect()
            ->route('login')
            ->with('success', 'Your password has been set successfully. You may now log in.');
    }
}
