<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear the roles table before seeding
        DB::table('roles')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'Super Admin', 'description' => 'Highest admin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Admin', 'description' => 'System Admin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Teacher', 'description' => 'School Teacher', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Student', 'description' => 'School Student', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'name' => 'Parent', 'description' => 'Student Parent', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
