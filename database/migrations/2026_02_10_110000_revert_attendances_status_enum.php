<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('attendances')
            ->whereIn('status', ['late', 'leave'])
            ->update(['status' => 'present']);

        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present','absent') NOT NULL DEFAULT 'present'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present','absent') NOT NULL DEFAULT 'present'");
    }
};
