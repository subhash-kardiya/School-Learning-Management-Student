<?php

namespace Database\Factories;

use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['A', 'B', 'C']),
            'class_id' => null,
            'capacity' => 15,
            'status' => 1,
        ];
    }
}

