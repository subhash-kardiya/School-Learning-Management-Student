<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'academic_year_id',
        'class_id',
        'section_id',
        'subject_name',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'room_no',
        'total_mark',
        'passing_mark',
        'result_declared',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'result_declared' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * Get the academic year that owns the exam.
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * Get the class that owns the exam.
     */
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    /**
     * Get the section that owns the exam.
     */
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
}
