<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'academic_year_id',
        'class_teacher_id',
        'status',
    ];

    // Optional: Relation with Teacher
    public function teacher() {
        return $this->belongsTo(Teacher::class, 'class_teacher_id');
    }

    // Optional: Relation with Academic Year
    public function academicYear() {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    // Relation with Sections
    public function sections() {
        return $this->hasMany(Section::class, 'class_id');
    }
}
