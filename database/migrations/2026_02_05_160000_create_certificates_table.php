<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('certificate_no')->unique();
            $table->enum('certificate_type', ['bonafide', 'leaving']);
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->onDelete('set null');
            $table->date('issue_date');
            $table->string('reason')->nullable();
            $table->string('conduct')->nullable();
            $table->string('remarks', 500)->nullable();
            $table->enum('status', ['draft', 'issued'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
