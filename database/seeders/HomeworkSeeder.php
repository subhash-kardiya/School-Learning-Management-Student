<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Homework;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\AcademicYear;

class HomeworkSeeder extends Seeder
{
    public function run(): void
    {
        $class1 = Classes::orderBy('id')->first();
        $class2 = Classes::orderBy('id')->skip(1)->first();
        $section1 = Section::orderBy('id')->first();
        $section2 = Section::orderBy('id')->skip(1)->first();
        $subject1 = Subject::where('class_id', $class1?->id)->first();
        $subject2 = Subject::where('class_id', $class2?->id)->first();
        $teacher1 = Teacher::orderBy('id')->first();
        $teacher2 = Teacher::orderBy('id')->skip(1)->first();
        $year = AcademicYear::first();

        if (!$class1 || !$section1 || !$subject1 || !$teacher1) {
            return;
        }

        Homework::updateOrCreate(
            [
                'title' => 'Chapter 1 Practice',
                'class_id' => $class1->id,
                'section_id' => $section1->id,
                'subject_id' => $subject1->id,
                'teacher_id' => $teacher1->id,
            ],
            [
                'academic_year_id' => $year?->id,
                'description' => 'Complete the assigned exercises.',
                'due_date' => now()->addDays(2)->toDateString(),
                'status' => 1,
            ]
        );

        if ($class2 && $section2 && $subject2 && $teacher2) {
            Homework::updateOrCreate(
                [
                    'title' => 'Worksheet A',
                    'class_id' => $class2->id,
                    'section_id' => $section2->id,
                    'subject_id' => $subject2->id,
                    'teacher_id' => $teacher2->id,
                ],
                [
                    'academic_year_id' => $year?->id,
                    'description' => 'Complete the assigned exercises.',
                    'due_date' => now()->addDays(3)->toDateString(),
                    'status' => 1,
                ]
            );
        }
    }
}
