<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('faculty', 'role')) {
            Schema::table('faculty', function (Blueprint $table) {
                $table->enum('role', [
                    'teacher',
                    'adviser',
                    'teacher_adviser'
                ])->default('teacher');
            });
        }
    }

    public function down(): void
    {
        Schema::table('faculty', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
