<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        if (Schema::hasTable('teacher_mappings') && !Schema::hasColumn('teacher_mappings', 'room_id')) {
            Schema::table('teacher_mappings', function (Blueprint $table) {
                $table->unsignedBigInteger('room_id')->nullable()->after('section_id');
            });
        }

        Schema::table('teacher_mappings', function (Blueprint $table) {
            $table->foreign('room_id')->references('id')->on('rooms')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('teacher_mappings')) {
            Schema::table('teacher_mappings', function (Blueprint $table) {
                $table->dropForeign(['room_id']);
            });
        }
        Schema::dropIfExists('rooms');
    }
};
