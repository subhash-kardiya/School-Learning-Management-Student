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
        Schema::create('sections', function (Blueprint $table) {
            $table->id(); // PK
            $table->string('name'); // Section name
            $table->unsignedInteger('capacity');
            $table->foreignId('class_id')
                ->constrained('classes')
                ->onDelete('cascade'); // FK → classes.id
            $table->boolean('status')->default(1); // Active / Inactive
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
