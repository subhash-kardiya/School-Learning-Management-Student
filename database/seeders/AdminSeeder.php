<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('admins')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Admin::updateOrCreate(
            ['email' => 'karadiyashubhash@gmail.com'],
            [
                'admin_name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make('superadmin'),
                'mobile_no' => '9999999999',
                'address' => 'Rajkot',
                'dob' => '1990-01-01',
                'role_id' => 1,
                'status' => 1,
            ]
        );

        Admin::updateOrCreate(
            ['email' => 'admin@school.com'],
            [
                'admin_name' => 'School Admin',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'mobile_no' => '9999991111',
                'address' => 'Rajkot',
                'dob' => '1992-01-01',
                'role_id' => 2,
                'status' => 1,
            ]
        );
    }
}
