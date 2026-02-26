<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'class_id',
        'status',
        'subject_code',
    ];

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function homeworks()
    {
        return $this->hasMany(Homework::class, 'subject_id');
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_mappings', 'subject_id', 'teacher_id')
            ->withPivot(['section_id', 'room_id'])
            ->withTimestamps();
    }
}
