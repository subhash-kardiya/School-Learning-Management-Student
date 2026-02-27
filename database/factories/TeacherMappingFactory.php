<?php

namespace Database\Factories;

use App\Models\TeacherMapping;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherMappingFactory extends Factory
{
    protected $model = TeacherMapping::class;

    public function definition(): array
    {
        return [
            'teacher_id' => null,
            'section_id' => null,
            'subject_id' => null,
            'room_id' => null,
        ];
    }
}

