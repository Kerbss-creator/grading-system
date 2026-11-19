<?php

namespace App\Services;

use App\Models\AccountSetupToken;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AccountSetupService
{
    public function sendSetupLink(User $user): void
    {
        // Generate a secure random token.
        $plainToken = Str::random(64);

        // Store only the hashed token.
        AccountSetupToken::updateOrCreate(
            ['user_id' => $user->id],
            [
                'token' => hash('sha256', $plainToken),
                'expires_at' => now()->addHour(),
                'used_at' => null,
            ]
        );

        // Generate the password setup URL.
        $setupUrl = route('account.setup.show', [
            'token' => $plainToken,
        ]);

        // Send the setup link to the user's email.
        Mail::raw(
            "Hello {$user->name},\n\n"
            . "Your account has been created for Forbes Academy Grading System.\n\n"
            . "Click the link below to set your password:\n"
            . "{$setupUrl}\n\n"
            . "This link will expire in 60 minutes and can only be used once.\n\n"
            . "If you did not expect this email, please ignore it.",
            function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Forbes Academy - Set Your Password');
            }
        );
    }
}
