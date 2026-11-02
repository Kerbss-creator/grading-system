<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
        foreach (['students', 'teachers'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
                $table->softDeletes();
            });
        }
        foreach (['students' => 'student_id', 'teachers' => 'teacher_id'] as $table => $idColumn) {
            $role = substr($table, 0, -1);
            foreach (DB::table($table)->get() as $row) {
                $email = $row->email ?: ($row->{$idColumn} . '@forbes.edu');
                $user = DB::table('users')->where('email', $email)->where('role', $role)->first();
                if ($user) {
                    DB::table($table)->where('id', $row->id)->update(['user_id' => $user->id]);
                }
            }
        }
    }

    public function down(): void
    {
        foreach (['students', 'teachers'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
                $table->dropSoftDeletes();
            });
        }
        Schema::table('users', fn (Blueprint $table) => $table->dropSoftDeletes());
    }
};
