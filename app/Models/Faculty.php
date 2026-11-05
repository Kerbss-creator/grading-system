<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faculty extends Model
{
    use SoftDeletes;
    protected $table = 'faculty';

    protected $fillable = [
        'faculty_id',
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'role',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function teachingAssignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class);
    }   

    public function advisoryAssignments(): HasMany
    {
        return $this->hasMany(AdvisoryAssignment::class);
    }
}
