<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('grade_records', function (Blueprint $table) {
            $table->text('adviser_return_reason')
                ->nullable()
                ->after('adviser_reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('grade_records', function (Blueprint $table) {
            $table->dropColumn('adviser_return_reason');
        });
    }
};