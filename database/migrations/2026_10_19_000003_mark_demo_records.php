<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['students', 'teachers'] as $type) {
            Schema::table($type, function (Blueprint $table) {
                $table->boolean('is_demo')->default(false)->index();
            });
            $prefix = $type === 'students' ? 'rene.student' : 'rene.teacher';
            DB::table($type)->where('email', 'like', $prefix . '%@example.test')->update(['is_demo' => true]);
        }
    }
    public function down(): void
    {
        foreach (['students', 'teachers'] as $type) {
            Schema::table($type, fn (Blueprint $table) => $table->dropColumn('is_demo'));
        }
    }
};
