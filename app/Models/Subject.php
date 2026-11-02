<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name', 'grade_level', 'school_year', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function offerings() { return $this->hasMany(SubjectOffering::class); }
}
