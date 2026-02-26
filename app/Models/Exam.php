<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'academic_year_id',
        'class_id',
        'section_id',
        'exam_type_id',
        'name',
        'start_date',
        'end_date',
        'result_publish_date',
        'status',
        'creator_role',
        'creator_id',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function examType()
    {
        return $this->belongsTo(ExamType::class, 'exam_type_id');
    }

    public function examSubjects()
    {
        return $this->hasMany(ExamSubject::class, 'exam_id');
    }

    public function marks()
    {
        return $this->hasMany(ExamMark::class, 'exam_id');
    }
}
