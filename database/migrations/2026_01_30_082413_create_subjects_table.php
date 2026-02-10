<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('subject_code')->nullable()->unique();

            $table->foreignId('class_id')
                ->constrained('classes')
                ->onDelete('cascade');

            $table->foreignId('section_id')
                ->nullable()
                ->constrained('sections')
                ->onDelete('set null');

            $table->foreignId('teacher_id')
                ->nullable()
                ->constrained('teachers')
                ->onDelete('set null');

            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
