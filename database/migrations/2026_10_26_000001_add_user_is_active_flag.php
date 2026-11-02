<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
        // Backfill accounts created before this migration from current student/teacher records.
        foreach (['students', 'teachers'] as $type) {
            DB::table($type)->where('status', 'inactive')->whereNotNull('user_id')
                ->orderBy('id')->chunk(200, function ($rows) {
                    DB::table('users')->whereIn('id', $rows->pluck('user_id')->all())->update(['is_active' => false]);
                });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
