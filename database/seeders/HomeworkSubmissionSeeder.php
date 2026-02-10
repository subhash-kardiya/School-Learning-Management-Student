<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HomeworkSubmission;
use App\Models\Homework;
use App\Models\Student;

class HomeworkSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $homework1 = Homework::orderBy('id')->first();
        $homework2 = Homework::orderBy('id')->skip(1)->first();
        $student1 = Student::orderBy('id')->first();
        $student2 = Student::orderBy('id')->skip(1)->first();

        if (!$homework1 || !$student1) {
            return;
        }

        HomeworkSubmission::updateOrCreate(
            [
                'homework_id' => $homework1->id,
                'student_id' => $student1->id,
            ],
            [
                'submitted_at' => now(),
                'status' => 'Submitted',
                'feedback' => 'Good work!',
            ]
        );

        if ($homework2 && $student2) {
            HomeworkSubmission::updateOrCreate(
                [
                    'homework_id' => $homework2->id,
                    'student_id' => $student2->id,
                ],
                [
                    'submitted_at' => now(),
                    'status' => 'Submitted',
                    'feedback' => 'Well done!',
                ]
            );
        }
    }
}
