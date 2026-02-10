<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Homework extends Model
{
    protected $table = 'homeworks';

    protected $fillable = [
        'teacher_id',
        'class_id',
        'section_id',
        'subject_id',
        'academic_year_id',
        'title',
        'description',
        'due_date',
        'attachment',
        'status',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function submissions()
    {
        return $this->hasMany(HomeworkSubmission::class, 'homework_id');
    }
}
