<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradePublication extends Model
{
    protected $fillable = [
        'school_year',
        'term',
        'published_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'term' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Administrator who published the grades.
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
