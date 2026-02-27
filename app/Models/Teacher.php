<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\HasPermissions;

class Teacher extends Authenticatable
{
    use HasFactory, HasPermissions;
    protected $fillable = [
        'name','username','email','password','mobile_no',
        'school_id',
        'gender','date_of_birth','address','city','state','pincode',
        'qualification','exp','join_date','profile_image','status'
    ];

    protected $hidden = ['password'];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_mappings', 'teacher_id', 'subject_id')
            ->withPivot(['section_id', 'room_id'])
            ->withTimestamps();
    }
}
