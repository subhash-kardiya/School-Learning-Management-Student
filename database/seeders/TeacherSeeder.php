<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Teacher;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'Teacher')->orWhere('name', 'teacher')->first();
        if (!$role) {
            return;
        }

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear the teachers table before seeding
        Teacher::truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $teachers = [
            ['name' => 'Demo Teacher', 'gender' => 'male', 'city' => 'Ahmedabad'],
            ['name' => 'Anjali Patel', 'gender' => 'female', 'city' => 'Surat'],
            ['name' => 'Ravi Mehta', 'gender' => 'male', 'city' => 'Rajkot'],
            ['name' => 'Neha Shah', 'gender' => 'female', 'city' => 'Vadodara'],
            ['name' => 'Kiran Joshi', 'gender' => 'male', 'city' => 'Jamnagar'],
        ];

        $i = 1;
        foreach ($teachers as $t) {
            Teacher::create([
                'role_id' => $role->id,
                'name' => $t['name'],
                'username' => 'teacher' . $i,
                'email' => 'teacher' . $i . '@school.com',
                'password' => Hash::make('teacher123'),
                'mobile_no' => '99900011' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'gender' => $t['gender'],
                'date_of_birth' => '1990-04-12',
                'address' => ($i * 10) . ' School Road',
                'city' => $t['city'],
                'state' => 'Gujarat',
                'pincode' => '380001',
                'qualification' => 'B.Ed',
                'exp' => 4 + ($i % 4),
                'join_date' => '2023-06-15',
                'profile_image' => null,
                'status' => 1,
            ]);
            $i++;
        }
    }

}
