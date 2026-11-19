<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE faculty
            MODIFY status ENUM('pending', 'active', 'inactive')
            NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        // Convert pending records before removing the enum value.
        DB::table('faculty')
            ->where('status', 'pending')
            ->update(['status' => 'inactive']);

        DB::statement("
            ALTER TABLE faculty
            MODIFY status ENUM('active', 'inactive')
            NOT NULL DEFAULT 'active'
        ");
    }
};