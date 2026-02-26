<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear the academic_years table before seeding
        DB::table('academic_years')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('academic_years')->insert([
            ['id' => 1, 'name' => '2024-2025', 'start_date' => '2024-06-01', 'end_date' => '2025-05-31', 'is_active' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => '2025-2026', 'start_date' => '2025-06-01', 'end_date' => '2026-05-31', 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
