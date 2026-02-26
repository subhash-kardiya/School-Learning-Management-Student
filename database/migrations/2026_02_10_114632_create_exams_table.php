<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id(); // PK

            $table->string('name');

            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')
                  ->onDelete('cascade');

            $table->foreignId('class_id')
                  ->constrained('classes')
                  ->onDelete('cascade');

            $table->foreignId('section_id')
                  ->nullable()
                  ->constrained('sections')
                  ->onDelete('cascade');
            $table->string('subject_name');
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('room_no');
            $table->integer('total_mark')->default(100);
            $table->integer('passing_mark')->default(33);
            $table->boolean('result_declared')->default(0);
            $table->boolean('status')->default(1); // Active / Inactive

            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
