<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\HasPermissions;

class Teacher extends Authenticatable
{
    use HasPermissions;
    protected $fillable = [
        'role_id','name','username','email','password','mobile_no',
        'gender','date_of_birth','address','city','state','pincode',
        'qualification','exp','join_date','profile_image','status'
    ];

    protected $hidden = ['password'];

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'teacher_id');
    }
}
