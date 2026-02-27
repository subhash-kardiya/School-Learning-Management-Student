<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classes;
use App\Models\TeacherMapping;
use App\Models\Timetable;
use App\Models\TimetableSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimetableSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('timetables')->truncate();
        DB::table('timetable_settings')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $activeYear = AcademicYear::where('is_active', 1)->first();
        if (!$activeYear) {
            return;
        }

        $classes = Classes::where('academic_year_id', $activeYear->id)
            ->with('sections')
            ->orderBy('id')
            ->get();

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $slots = [
            ['start' => '09:00', 'end' => '09:45', 'type' => 'period'],
            ['start' => '09:45', 'end' => '10:30', 'type' => 'period'],
            ['start' => '10:30', 'end' => '10:45', 'type' => 'break'],
            ['start' => '10:45', 'end' => '11:30', 'type' => 'period'],
            ['start' => '11:30', 'end' => '12:15', 'type' => 'period'],
            ['start' => '12:15', 'end' => '13:00', 'type' => 'lunch'],
            ['start' => '13:00', 'end' => '13:45', 'type' => 'period'],
        ];
        $teacherBusy = [];

        foreach ($classes as $class) {
            foreach ($class->sections as $section) {
                TimetableSetting::updateOrCreate(
                    ['academic_year_id' => $activeYear->id, 'class_id' => $class->id, 'section_id' => $section->id],
                    [
                        'days' => $days,
                        'slots' => $slots,
                        'status' => 'draft',
                    ]
                );

                $mappings = TeacherMapping::with('room')
                    ->where('section_id', $section->id)
                    ->orderBy('id')
                    ->get();

                if ($mappings->isEmpty()) {
                    continue;
                }

                $periodSlots = collect($slots)->filter(fn($slot) => ($slot['type'] ?? 'period') === 'period')->values();
                foreach ($days as $dayIndex => $day) {
                    // Day-wise deterministic shuffle so each day gets a different pattern.
                    $daySortedMappings = $mappings
                        ->sortBy(fn($map) => (($map->id + $class->id + $section->id + ($dayIndex * 7)) % 97))
                        ->values();

                    $previousTeacherId = null;
                    foreach ($periodSlots as $slotIndex => $slot) {
                        $start = $slot['start'];
                        $busyTeachers = $teacherBusy[$day][$start] ?? [];
                        $rotation = ($slotIndex + $dayIndex) % $daySortedMappings->count();
                        $candidatePool = $daySortedMappings
                            ->slice($rotation)
                            ->concat($daySortedMappings->slice(0, $rotation));

                        // Prefer: non-busy teacher and not consecutive with previous slot.
                        $candidate = $candidatePool
                            ->first(fn($map) => !in_array($map->teacher_id, $busyTeachers, true) && $map->teacher_id !== $previousTeacherId);

                        // Fallback: non-busy teacher (if all candidates are consecutive).
                        if (!$candidate) {
                            $candidate = $candidatePool
                                ->first(fn($map) => !in_array($map->teacher_id, $busyTeachers, true));
                        }

                        // Hard rule: if no free teacher for this slot, skip lecture to avoid clashes.
                        if (!$candidate) {
                            continue;
                        }

                        $mapping = $candidate;
                        $previousTeacherId = $mapping->teacher_id;

                        if (!isset($teacherBusy[$day][$start])) {
                            $teacherBusy[$day][$start] = [];
                        }
                        $teacherBusy[$day][$start][] = $mapping->teacher_id;

                        Timetable::create([
                            'class_id' => $class->id,
                            'section_id' => $section->id,
                            'subject_id' => $mapping->subject_id,
                            'teacher_id' => $mapping->teacher_id,
                            'academic_year_id' => $activeYear->id,
                            'day_of_week' => $day,
                            'start_time' => $slot['start'],
                            'end_time' => $slot['end'],
                            'room' => $mapping->room?->name,
                            'status' => 1,
                            'type' => 'lecture',
                        ]);
                    }
                }
            }
        }
    }
}
