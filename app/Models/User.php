<?php

namespace App\Models;

use Database\Factories\UserFactory; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'account_status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function faculty(): HasOne
    {
        return $this->hasOne(Faculty::class, 'user_id');
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'user_id');
    }

<<<<<<< HEAD
    public function faculty(): HasOne
    {
        return $this->hasOne(Faculty::class, 'user_id');
=======
    public function accountSetupToken(): HasOne
    {
        return $this->hasOne(AccountSetupToken::class);
    }

    public function isAccountActive(): bool
    {
        return $this->account_status === 'active';
    }

    public function isAccountPending(): bool
    {
        return $this->account_status === 'pending';
    }

    public function isAccountInactive(): bool
    {
        return $this->account_status === 'inactive';
>>>>>>> grading-system2
    }
}
