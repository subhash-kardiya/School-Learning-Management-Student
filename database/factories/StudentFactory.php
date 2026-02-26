<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'role_id' => null,
            'student_name' => fake()->name(),
            'roll_no' => (string) fake()->numberBetween(1, 60),
            'username' => 'student' . fake()->unique()->numberBetween(1000, 9999),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('student123'),
            'mobile_no' => '9' . fake()->numerify('#########'),
            'gender' => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->date('Y-m-d', '2017-12-31'),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'pincode' => fake()->postcode(),
            'profile_image' => null,
            'class_id' => null,
            'section_id' => null,
            'academic_year_id' => null,
            'parent_id' => null,
            'status' => 1,
        ];
    }
}

