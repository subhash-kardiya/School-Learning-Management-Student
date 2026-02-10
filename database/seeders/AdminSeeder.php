<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear the admins table before seeding
        Admin::truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Super Admin
        Admin::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'admin_name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => Hash::make('superadmin'),
                'mobile_no' => '9999999999',
                'address' => '150 feet Ring Road, Rajkot',
                'dob' => '1990-08-20',
                'profile_image' => null,
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
                'address' => '123 Main Street, Rajkot',
                'dob' => '1985-05-10',
                'profile_image' => null,
                'role_id' => 2,
                'status' => 1,
            ]
        );
    }
}
