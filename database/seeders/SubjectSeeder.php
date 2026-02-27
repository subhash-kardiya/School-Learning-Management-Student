<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Classes;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear the subjects table before seeding
        DB::table('subjects')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $class1 = Classes::orderBy('id')->first();
        if (!$class1) {
            return;
        }

        $subjects = [
            ['name' => 'Gujarati', 'code' => 'GUJ'],
            ['name' => 'Hindi', 'code' => 'HIN'],
            ['name' => 'English', 'code' => 'ENG'],
            ['name' => 'Mathematics', 'code' => 'MATH'],
            ['name' => 'Science', 'code' => 'SCI'],
        ];

        foreach ($subjects as $subject) {
            $code = $subject['code'] . '-' . $class1->id;
            Subject::create([
                'name' => $subject['name'],
                'class_id' => $class1->id,
                'subject_code' => $code,
                'status' => 1,
            ]);
        }
    }
}
