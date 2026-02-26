<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = ['name', 'start_mark', 'end_mark', 'description'];

    public static function resolveGrade($percentage)
    {
        $percentage = (float) $percentage;

        if (\Illuminate\Support\Facades\Schema::hasTable('grades')) {
            // Try to get from database
            $match = self::where('start_mark', '<=', $percentage)
                        ->where('end_mark', '>=', $percentage)
                        ->first();

            if ($match) {
                return [
                    'name' => $match->name,
                    'description' => $match->description
                ];
            }
        }

        // Fallback default rules if DB is empty or no match
        $defaults = [
            ['name' => 'A', 'start_mark' => 90, 'end_mark' => 100, 'description' => 'Excellent'],
            ['name' => 'B', 'start_mark' => 80, 'end_mark' => 89.99, 'description' => 'Good'],
            ['name' => 'C', 'start_mark' => 70, 'end_mark' => 79.99, 'description' => 'Fair/Average'],
            ['name' => 'D', 'start_mark' => 60, 'end_mark' => 69.99, 'description' => 'Poor/Barely Passing'],
            ['name' => 'F', 'start_mark' => 0, 'end_mark' => 59.99, 'description' => 'Fail'],
        ];

        foreach ($defaults as $rule) {
            if ($percentage >= $rule['start_mark'] && $percentage <= $rule['end_mark']) {
                return [
                    'name' => $rule['name'],
                    'description' => $rule['description']
                ];
            }
        }

        return ['name' => '-', 'description' => ''];
    }
}
