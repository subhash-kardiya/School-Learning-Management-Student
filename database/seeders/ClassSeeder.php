<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicYear;
use App\Models\Teacher;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear the classes table before seeding
        DB::table('classes')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $year = AcademicYear::where('is_active', 1)->first() ?? AcademicYear::first();
        $teacherId = Teacher::first()?->id;

        DB::table('classes')->insert([
            [
                'name' => 'Class 1',
                'academic_year_id' => $year?->id ?? 1,
                'class_teacher_id' => $teacherId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Class 2',
                'academic_year_id' => $year?->id ?? 1,
                'class_teacher_id' => $teacherId,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
