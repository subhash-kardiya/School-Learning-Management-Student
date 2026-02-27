<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\ParentModel;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class ParentSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'Parent')->orWhere('name', 'parent')->first();
        if (!$role) {
            return;
        }

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear the parents table before seeding
        ParentModel::truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        ParentModel::create([
            'role_id' => $role->id,
            'parent_name' => 'Demo Parent',
            'username' => 'parent1',
            'email' => 'parent1@school.com',
            'password' => Hash::make('parent123'),
            'mobile_no' => '9990002201',
            'gender' => 'female',
            'date_of_birth' => '1988-03-20',
            'address' => '12 Rose Garden Society',
            'city' => 'Vadodara',
            'state' => 'Gujarat',
            'pincode' => '390001',
            'profile_image' => null,
            'status' => 1,
        ]);

        ParentModel::create([
            'role_id' => $role->id,
            'parent_name' => 'Rajesh Kumar',
            'username' => 'parent2',
            'email' => 'parent2@school.com',
            'password' => Hash::make('parent123'),
            'mobile_no' => '9990002202',
            'gender' => 'male',
            'date_of_birth' => '1985-11-08',
            'address' => '45 Riverfront Road',
            'city' => 'Rajkot',
            'state' => 'Gujarat',
            'pincode' => '360001',
            'profile_image' => null,
            'status' => 1,
        ]);
    }
}
