<?php

namespace Database\Factories;

use App\Models\Homework;
use Illuminate\Database\Eloquent\Factories\Factory;

class HomeworkFactory extends Factory
{
    protected $model = Homework::class;

    public function definition(): array
    {
        return [
            'teacher_id' => null,
            'class_id' => null,
            'section_id' => null,
            'subject_id' => null,
            'academic_year_id' => null,
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'due_date' => fake()->dateTimeBetween('+3 days', '+12 days')->format('Y-m-d'),
            'attachment' => null,
            'status' => 1,
        ];
    }
}

