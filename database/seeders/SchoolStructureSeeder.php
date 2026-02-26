<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\Homework;
use App\Models\HomeworkSubmission;
use App\Models\ParentModel;
use App\Models\Role;
use App\Models\Room;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherMapping;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolStructureSeeder extends Seeder
{
    public function run(): void
    {
        $subjectMapByStandard = [
            1 => ['Gujarati', 'English', 'Mathematics', 'EVS', 'Drawing'],
            2 => ['Gujarati', 'English', 'Mathematics', 'EVS', 'Computer'],
            3 => ['Gujarati', 'English', 'Mathematics', 'Science', 'Social Science'],
            4 => ['Gujarati', 'English', 'Mathematics', 'Science', 'Computer'],
            5 => ['Gujarati', 'English', 'Mathematics', 'Science', 'Social Science'],
            6 => ['Gujarati', 'English', 'Mathematics', 'Science', 'Social Science'],
            7 => ['Gujarati', 'English', 'Mathematics', 'Science', 'Social Science'],
            8 => ['Gujarati', 'English', 'Mathematics', 'Science', 'Social Science'],
            9 => ['Gujarati', 'English', 'Mathematics', 'Science', 'Social Science'],
            10 => ['Gujarati', 'English', 'Mathematics', 'Science', 'Social Science'],
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach ([
            'homework_submissions',
            'homeworks',
            'teacher_mappings',
            'students',
            'parents',
            'subjects',
            'sections',
            'classes',
            'teachers',
            'rooms',
            'academic_years',
        ] as $table) {
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $teacherRoleId = (int) (Role::whereRaw('LOWER(name) = ?', ['teacher'])->value('id') ?? 3);
        $studentRoleId = (int) (Role::whereRaw('LOWER(name) = ?', ['student'])->value('id') ?? 4);
        $parentRoleId = (int) (Role::whereRaw('LOWER(name) = ?', ['parent'])->value('id') ?? 5);

        // 1) Academic Years = 2
        $years = AcademicYear::factory()
            ->count(2)
            ->sequence(
                [
                    'name' => '2025-2026',
                    'start_date' => '2025-06-01',
                    'end_date' => '2026-05-31',
                    'is_active' => 0,
                    'is_locked' => 0,
                ],
                [
                    'name' => '2026-2027',
                    'start_date' => '2026-06-01',
                    'end_date' => '2027-05-31',
                    'is_active' => 1,
                    'is_locked' => 0,
                ]
            )
            ->create();
        $activeYear = $years->last();

        // 2) Teachers = 20
        $teachers = Teacher::factory()
            ->count(20)
            ->sequence(fn($seq) => [
                'role_id' => $teacherRoleId,
                'username' => 'teacher' . ($seq->index + 1),
                'email' => 'teacher' . ($seq->index + 1) . '@school.test',
                'status' => 1,
            ])
            ->create();

        // Rooms = 30
        $rooms = Room::factory()
            ->count(30)
            ->sequence(fn($seq) => [
                'name' => 'Room-' . ($seq->index + 1),
                'capacity' => 20,
                'status' => 1,
            ])
            ->create();

        // 3) Classes = 10 (Standard 1..10)
        $classes = Classes::factory()
            ->count(10)
            ->sequence(fn($seq) => [
                'name' => 'Standard ' . ($seq->index + 1),
                'academic_year_id' => $activeYear->id,
                'class_teacher_id' => $teachers[$seq->index % $teachers->count()]->id,
                'status' => 1,
            ])
            ->create();

        foreach ($classes as $classIndex => $class) {
            $standardNo = $classIndex + 1;
            $subjectsForClass = $subjectMapByStandard[$standardNo] ?? ['Gujarati', 'English', 'Mathematics', 'Science', 'Social Science'];

            // 4) Sections per class = A,B
            $sections = Section::factory()
                ->count(2)
                ->sequence(
                    ['name' => 'A', 'class_id' => $class->id, 'capacity' => 15, 'status' => 1],
                    ['name' => 'B', 'class_id' => $class->id, 'capacity' => 15, 'status' => 1]
                )
                ->create();

            // 5) Subjects per class = 5
            $subjects = Subject::factory()
                ->count(5)
                ->sequence(fn($seq) => [
                    'name' => $subjectsForClass[$seq->index] ?? ('Subject ' . ($seq->index + 1)),
                    'subject_code' => 'STD' . str_pad((string) $standardNo, 2, '0', STR_PAD_LEFT) . '-SUB' . ($seq->index + 1),
                    'class_id' => $class->id,
                    'section_id' => null,
                    'status' => 1,
                ])
                ->create();

            // 6) Students full + 7) one parent per student
            foreach ($sections as $section) {
                for ($st = 1; $st <= 15; $st++) {
                    $counter = (($class->id - 1) * 30) + (($section->name === 'A' ? 0 : 15) + $st);
                    $studentName = fake('en_IN')->name();
                    $parentName = fake('en_IN')->name();
                    $studentUsername = 'student' . $counter;
                    $parentUsername = 'parent' . $counter;

                    $parent = ParentModel::factory()->state([
                        'parent_name' => $parentName,
                        'username' => $parentUsername,
                        'email' => 'parent' . $counter . '@school.test',
                        'role_id' => $parentRoleId,
                        'status' => 1,
                    ])->create();

                    Student::factory()->state([
                        'student_name' => $studentName,
                        'username' => $studentUsername,
                        'email' => 'student' . $counter . '@school.test',
                        'role_id' => $studentRoleId,
                        'class_id' => $class->id,
                        'section_id' => $section->id,
                        'academic_year_id' => $activeYear->id,
                        'parent_id' => $parent->id,
                        'roll_no' => str_pad((string) $st, 2, '0', STR_PAD_LEFT),
                        'status' => 1,
                    ])->create();
                }
            }

            // 8) Teacher mapping auto
            $mapIndex = 0;
            $subjectTeacherPool = $teachers->shuffle()->values();
            $subjectTeacherById = [];
            foreach ($subjects as $subject) {
                // Keep teacher different subject-wise inside the class.
                $subjectTeacherById[$subject->id] = $subjectTeacherPool[$mapIndex % $subjectTeacherPool->count()]->id;
                $mapIndex++;
            }

            $mapIndex = 0;
            foreach ($subjects as $subject) {
                foreach ($sections as $section) {
                    TeacherMapping::factory()->state([
                        'teacher_id' => $subjectTeacherById[$subject->id],
                        'subject_id' => $subject->id,
                        'section_id' => $section->id,
                        'room_id' => $rooms[$mapIndex % $rooms->count()]->id,
                    ])->create();
                    $mapIndex++;
                }
            }

            // 9) Homework one per class
            $sectionA = $sections->firstWhere('name', 'A');
            $subject1 = $subjects->first();
            $mapped = TeacherMapping::where('section_id', $sectionA?->id)
                ->where('subject_id', $subject1?->id)
                ->first();

            if (!$sectionA || !$subject1 || !$mapped) {
                continue;
            }

            $homework = Homework::factory()->state([
                'title' => 'Homework for ' . $class->name,
                'teacher_id' => $mapped->teacher_id,
                'class_id' => $class->id,
                'section_id' => $sectionA->id,
                'subject_id' => $subject1->id,
                'academic_year_id' => $activeYear->id,
                'due_date' => now()->addDays(7)->toDateString(),
                'status' => 1,
            ])->create();

            // 10) submissions
            $students = Student::where('class_id', $class->id)
                ->where('section_id', $sectionA->id)
                ->inRandomOrder()
                ->limit(10)
                ->get();

            foreach ($students as $student) {
                HomeworkSubmission::factory()->state([
                    'homework_id' => $homework->id,
                    'student_id' => $student->id,
                    'submitted_at' => now()->subDays(rand(0, 2)),
                    'status' => 'Submitted',
                ])->create();
            }
        }
    }
}
