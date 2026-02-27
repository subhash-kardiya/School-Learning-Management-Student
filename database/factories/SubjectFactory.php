<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        $subjectNames = ['Gujarati', 'English', 'Mathematics', 'Science', 'Social Science', 'Computer', 'Drawing'];

        return [
            'name' => fake()->randomElement($subjectNames),
            'subject_code' => strtoupper(fake()->bothify('SUB-###??')),
            'class_id' => null,
            'section_id' => null,
            'status' => 1,
        ];
    }
}
