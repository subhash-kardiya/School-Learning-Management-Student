<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\HasPermissions;

class Admin extends Authenticatable
{
    use HasPermissions;
    protected $table = 'admins';

    protected $fillable = [
        'admin_name',
        'username',
        'email',
        'password',
        'mobile_no',
        'profile_image',
        'role_id',
        'status',
    ];

    protected $hidden = [
        'password',
    ];
}
