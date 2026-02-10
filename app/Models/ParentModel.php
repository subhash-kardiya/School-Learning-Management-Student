<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\HasPermissions;

class ParentModel extends Authenticatable
{
    use HasFactory, HasPermissions;

    // Correct table name
    protected $table = 'parents';

    protected $fillable = [
        'role_id',
        'parent_name',
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
        'status',
    ];

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }
}
