
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
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('faculty', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
