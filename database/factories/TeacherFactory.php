<?php

namespace Database\Factories;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        return [
            'role_id' => null,
            'name' => fake()->name(),
            'username' => 'teacher' . fake()->unique()->numberBetween(1000, 9999),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('teacher123'),
            'mobile_no' => '9' . fake()->numerify('#########'),
            'gender' => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->date('Y-m-d', '2000-01-01'),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'pincode' => fake()->postcode(),
            'qualification' => fake()->randomElement(['B.Ed', 'M.Ed', 'M.Sc', 'M.A']),
            'exp' => fake()->numberBetween(2, 15),
            'join_date' => fake()->date('Y-m-d', 'now'),
            'profile_image' => null,
            'status' => 1,
        ];
    }
}

