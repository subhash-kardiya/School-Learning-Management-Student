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
        Schema::create('classes', function (Blueprint $table) {
            $table->id(); // PK: id
            $table->string('name'); // Class name
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade'); // FK → academic_years
            $table->foreignId('class_teacher_id')->nullable()->constrained('teachers')->onDelete('set null'); // FK → teachers, nullable
            $table->boolean('status')->default(1); // Active/inactive
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
