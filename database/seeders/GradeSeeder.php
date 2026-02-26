<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [
            ['name' => 'A', 'start_mark' => 90, 'end_mark' => 100, 'description' => 'Excellent'],
            ['name' => 'B', 'start_mark' => 80, 'end_mark' => 89.99, 'description' => 'Good'],
            ['name' => 'C', 'start_mark' => 70, 'end_mark' => 79.99, 'description' => 'Fair/Average'],
            ['name' => 'D', 'start_mark' => 60, 'end_mark' => 69.99, 'description' => 'Poor/Barely Passing'],
            ['name' => 'F', 'start_mark' => 0, 'end_mark' => 59.99, 'description' => 'Fail'],
        ];

        foreach ($grades as $grade) {
            Grade::updateOrCreate(['name' => $grade['name']], $grade);
        }
    }
}
