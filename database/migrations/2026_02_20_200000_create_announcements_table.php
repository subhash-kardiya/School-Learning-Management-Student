<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('announcements')) {
            return;
        }

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');

            // Core ERP levels
            $table->enum('level', ['system', 'school', 'class'])->default('school');
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();

            // Target audience
            $table->enum('target_role', ['all', 'teacher', 'student', 'parent'])->default('all');

            // Backward compatibility for existing view/controller fallback
            $table->enum('role_type', ['all', 'teacher', 'student', 'parent'])->default('all');

            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_role', 30)->nullable();

            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['level', 'status']);
            $table->index(['school_id', 'status']);
            $table->index(['class_id', 'status']);
            $table->index(['target_role', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};

