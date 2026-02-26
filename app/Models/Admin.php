<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\HasPermissions;

class Admin extends Authenticatable
{
    use HasFactory, HasPermissions;
    protected $table = 'admins';

    protected $fillable = [
        'admin_name',
        'username',
        'email',
        'password',
        'mobile_no',
        'address',
        'dob',
        'profile_image',
        'role_id',
        'school_id',
        'status',
    ];

    protected $hidden = [
        'password',
    ];
}
