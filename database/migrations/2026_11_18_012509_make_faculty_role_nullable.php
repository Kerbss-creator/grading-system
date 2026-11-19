<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faculty', function (Blueprint $table) {
            $table->enum('role', [
                'teacher',
                'adviser',
                'teacher_adviser',
            ])->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        // Restore the previous default role.
        // Existing NULL roles must be converted before rollback.
        Schema::table('faculty', function (Blueprint $table) {
            $table->enum('role', [
                'teacher',
                'adviser',
                'teacher_adviser',
            ])->default('teacher')->change();
        });
    }
};
