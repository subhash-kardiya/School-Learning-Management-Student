<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Classes;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear the sections table before seeding
        DB::table('sections')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $classes = Classes::orderBy('id')->take(2)->get();
        if ($classes->isEmpty()) {
            return;
        }

        $rows = [];
        foreach ($classes as $class) {
            foreach (['A', 'B', 'C'] as $name) {
                $rows[] = [
                    'name' => $name,
                    'class_id' => $class->id,
                    'capacity' => 40,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('sections')->insert($rows);

    }
}
