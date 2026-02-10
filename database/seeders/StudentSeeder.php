<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Role;
use App\Models\Classes;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\ParentModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'Student')->orWhere('name', 'student')->first();
        if (!$role) {
            return;
        }

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear the students table before seeding
        Student::truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $class1 = Classes::orderBy('id')->first();
        $class2 = Classes::orderBy('id')->skip(1)->first();
        $section1 = Section::orderBy('id')->first();
        $section2 = Section::orderBy('id')->skip(1)->first();
        $year = AcademicYear::where('is_active', 1)->first() ?? AcademicYear::first();
        $parent1 = ParentModel::orderBy('id')->first();
        $parent2 = ParentModel::orderBy('id')->skip(1)->first();

        if (!$class1 || !$section1 || !$year) {
            return;
        }

        Student::create([
            'role_id' => $role->id,
            'student_name' => 'Demo Student',
            'roll_no' => 'R001',
            'username' => 'student01',
            'email' => 'student01@school.com',
            'password' => Hash::make('Student@123'),
            'mobile_no' => '987650001',
            'gender' => 'male',
            'date_of_birth' => '2008-06-15',
            'address' => 'School Road',
            'city' => 'Vadodara',
            'state' => 'Gujarat',
            'pincode' => '390001',
            'profile_image' => 'default_student.png',
            'class_id' => $class1->id,
            'section_id' => $section1->id,
            'academic_year_id' => $year->id,
            'parent_id' => $parent1?->id,
            'status' => 1,
        ]);

        Student::create([
            'role_id' => $role->id,
            'student_name' => 'Second Student',
            'roll_no' => 'R002',
            'username' => 'student02',
            'email' => 'student02@school.com',
            'password' => Hash::make('Student@123'),
            'mobile_no' => '987650002',
            'gender' => 'female',
            'date_of_birth' => '2008-08-20',
            'address' => 'School Road',
            'city' => 'Vadodara',
            'state' => 'Gujarat',
            'pincode' => '390001',
            'profile_image' => 'default_student.png',
            'class_id' => ($class2?->id ?? $class1->id),
            'section_id' => ($section2?->id ?? $section1->id),
            'academic_year_id' => $year->id,
            'parent_id' => $parent2?->id ?? $parent1?->id,
            'status' => 1,
        ]);
    }
}
