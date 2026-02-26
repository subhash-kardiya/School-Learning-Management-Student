<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::query()->inRandomOrder()->value('id'),
            'date' => $this->faker->dateTimeBetween('-60 days', 'now')->format('Y-m-d'),
            'status' => $this->faker->randomElement(['present', 'absent', 'present', 'absent']),
        ];
    }
}

