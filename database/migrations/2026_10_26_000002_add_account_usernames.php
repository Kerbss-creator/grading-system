<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 64)->nullable()->unique();
        });
        // Deterministic, collision-free usernames for existing records. No passwords are changed.
        DB::table('users')->select(['id', 'role'])->orderBy('id')->chunkById(200, function ($users) {
            foreach ($users as $user) {
                $prefix = match ($user->role) {
                    'student' => 'student', 'teacher' => 'teacher', 'admin' => 'admin', default => 'user',
                };
                DB::table('users')->where('id', $user->id)->update(['username' => $prefix . '_' . $user->id]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('username'));
    }
};
