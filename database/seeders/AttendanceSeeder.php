<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Student;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $student1 = Student::orderBy('id')->first();
        $student2 = Student::orderBy('id')->skip(1)->first();
        if (!$student1) {
            return;
        }

        Attendance::updateOrCreate(
            [
                'student_id' => $student1->id,
                'date' => now()->toDateString(),
            ],
            [
                'status' => 'present',
            ]
        );

        if ($student2) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $student2->id,
                    'date' => now()->toDateString(),
                ],
                [
                    'status' => 'absent',
                ]
            );
        }
    }
}
