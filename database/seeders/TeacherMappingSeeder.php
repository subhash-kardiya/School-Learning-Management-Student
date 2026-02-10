<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TeacherMapping;
use App\Models\Teacher;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class TeacherMappingSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = Teacher::orderBy('id')->get();
        $sections = Section::orderBy('id')->get();
        if ($teachers->isEmpty() || $sections->isEmpty()) {
            return;
        }

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear the teacher_mappings table before seeding
        DB::table('teacher_mappings')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $teacherIndex = 0;
        $class1 = Classes::orderBy('id')->first();
        if (!$class1) {
            return;
        }
        $sectionA = Section::where('class_id', $class1->id)->where('name', 'A')->first();
        if (!$sectionA) {
            return;
        }

        $subjects = Subject::where('class_id', $class1->id)->orderBy('id')->take(5)->get();
        foreach ($subjects as $subject) {
            $teacher = $teachers[$teacherIndex % $teachers->count()];
            $teacherIndex++;
            TeacherMapping::create([
                'teacher_id' => $teacher->id,
                'section_id' => $sectionA->id,
                'subject_id' => $subject->id,
            ]);
        }
    }
}
