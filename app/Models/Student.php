<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\HasPermissions;

class Student extends Authenticatable
{
    use HasFactory, HasPermissions;

    protected $fillable = [
        'role_id',
        'student_name',
        'roll_no',
        'username',
        'email',
        'password',
        'mobile_no',
        'gender',
        'date_of_birth',
        'address',
        'city',
        'state',
        'pincode',
        'profile_image',
        'class_id',
        'section_id',
        'academic_year_id',
        'parent_id',
        'status',
    ];

    protected $hidden = ['password'];

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function parent()
    {
        return $this->belongsTo(ParentModel::class, 'parent_id');
    }
}
