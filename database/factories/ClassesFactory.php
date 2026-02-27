<?php

namespace Database\Factories;

use App\Models\Classes;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassesFactory extends Factory
{
    protected $model = Classes::class;

    public function definition(): array
    {
        return [
            'name' => 'Standard ' . fake()->numberBetween(1, 12),
            'academic_year_id' => null,
            'class_teacher_id' => null,
            'status' => 1,
        ];
    }
}
