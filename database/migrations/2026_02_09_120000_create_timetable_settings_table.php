<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->json('days')->nullable();
            $table->json('slots')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->unique(['academic_year_id', 'class_id', 'section_id'], 'timetable_settings_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_settings');
    }
};
