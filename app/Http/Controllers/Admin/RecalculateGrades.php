<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ExamMark;
use App\Models\Exam;
use Illuminate\Support\Facades\DB;

class RecalculateGrades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grades:recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate grades and remarks for all existing exam marks based on the current rules.';

    /**
     * The default grading rules.
     *
     * @return array
     */
    private function defaultGradeRules(): array
    {
        return [
            ['name' => 'A', 'start_mark' => 90, 'end_mark' => 100, 'description' => 'Excellent'],
            ['name' => 'B', 'start_mark' => 80, 'end_mark' => 89.99, 'description' => 'Good'],
            ['name' => 'C', 'start_mark' => 70, 'end_mark' => 79.99, 'description' => 'Fair/Average'],
            ['name' => 'D', 'start_mark' => 60, 'end_mark' => 69.99, 'description' => 'Poor/Barely Passing'],
            ['name' => 'F', 'start_mark' => 0, 'end_mark' => 59.99, 'description' => 'Fail'],
        ];
    }

    /**
     * Resolve grade from percentage.
     *
     * @param float $percentage
     * @param \Illuminate\Support\Collection $gradeRules
     * @return array|null
     */
    private function resolveGradeFromPercentage(float $percentage, \Illuminate\Support\Collection $gradeRules): ?array
    {
        $gradeInfo = \App\Models\Grade::resolveGrade($percentage);
        
        if ($gradeInfo['name'] === '-') {
            return null;
        }

        return $gradeInfo;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting grade recalculation...');

        $gradeRules = collect($this->defaultGradeRules());
        $examMarks = ExamMark::with('exam')->get();
        $updatedCount = 0;

        DB::transaction(function () use ($examMarks, $gradeRules, &$updatedCount) {
            foreach ($examMarks as $mark) {
                if ($mark->exam && $mark->exam->total_mark > 0 && !is_null($mark->marks_obtained)) {
                    $percentage = round(($mark->marks_obtained / $mark->exam->total_mark) * 100, 2);
                    $gradeInfo = $this->resolveGradeFromPercentage($percentage, $gradeRules);

                    if ($gradeInfo) {
                        $mark->grade = $gradeInfo['name'];
                        // Only update remarks if they are empty or match an old grade description
                        if (empty($mark->remarks) || in_array($mark->remarks, ['Excellent', 'Good', 'Fair/Average', 'Poor/Barely Passing', 'Fail'])) {
                            $mark->remarks = $gradeInfo['description'];
                        }
                        $mark->save();
                        $updatedCount++;
                    }
                }
            }
        });

        $this->info("Recalculation complete. Updated {$updatedCount} records.");
        return 0;
    }
}
