<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamSubject extends Model
{
    protected $fillable = [
        'exam_id',
        'subject_id',
        'theory_marks',
        'practical_marks',
        'internal_marks',
        'passing_marks',
        'total_marks',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function marks()
    {
        return $this->hasMany(ExamMark::class, 'exam_subject_id');
    }
}
