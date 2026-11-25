<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    public const STATUS_UPCOMING = 'upcoming';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'year_name',
        'start_date',
        'end_date',
        'status',
        'current_term',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'current_term' => 'integer',
        ];
    }

    public static function active(): ?self
    {
        return static::where('status', self::STATUS_ACTIVE)->first();
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function hasValidCurrentTerm(): bool
    {
        return in_array($this->current_term, [1, 2, 3], true);
    }

    public function getCurrentTermLabelAttribute(): string
    {
        return match ($this->current_term) {
            1 => 'Term 1',
            2 => 'Term 2',
            3 => 'Term 3',
            default => 'Not Set',
        };
    }
}
