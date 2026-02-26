<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $studentIds = Student::query()
            ->where('status', 1)
            ->pluck('id');

        if ($studentIds->isEmpty()) {
            $this->command?->warn('AttendanceSeeder skipped: no active students found.');
            return;
        }

        DB::table('attendances')->truncate();

        $startDate = Carbon::today()->subDays(59);
        $endDate = Carbon::today();
        $rows = [];

        foreach ($studentIds as $studentId) {
            $cursor = $startDate->copy();

            while ($cursor->lte($endDate)) {
                // No attendance on Sundays.
                if ($cursor->isSunday()) {
                    $cursor->addDay();
                    continue;
                }

                // Slightly lower attendance chance on Saturday for realistic distribution.
                $presentChance = $cursor->isSaturday() ? 78 : 91;
                $status = random_int(1, 100) <= $presentChance ? 'present' : 'absent';

                $rows[] = Attendance::factory()->make([
                    'student_id' => $studentId,
                    'date' => $cursor->toDateString(),
                    'status' => $status,
                ])->toArray();

                if (count($rows) >= 1500) {
                    DB::table('attendances')->insert($rows);
                    $rows = [];
                }

                $cursor->addDay();
            }
        }

        if (!empty($rows)) {
            DB::table('attendances')->insert($rows);
        }

        $this->command?->info('AttendanceSeeder completed: sample attendance generated for last 60 days.');
    }
}
