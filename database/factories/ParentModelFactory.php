<?php

namespace Database\Factories;

use App\Models\ParentModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class ParentModelFactory extends Factory
{
    protected $model = ParentModel::class;

    public function definition(): array
    {
        return [
            'role_id' => null,
            'parent_name' => fake()->name(),
            'username' => 'parent' . fake()->unique()->numberBetween(1000, 9999),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('parent123'),
            'mobile_no' => '9' . fake()->numerify('#########'),
            'address' => fake()->address(),
            'status' => 1,
        ];
    }
}

