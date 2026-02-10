<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeworkSubmission extends Model
{
    protected $fillable = [
        'homework_id',
        'student_id',
        'submitted_at',
        'attachment',
        'feedback',
        'status',
    ];

    public function homework()
    {
        return $this->belongsTo(Homework::class, 'homework_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
