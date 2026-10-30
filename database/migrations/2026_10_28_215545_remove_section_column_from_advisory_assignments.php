<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advisory_assignments', function (Blueprint $table) {
            $table->dropColumn('section');
        });
    }

    public function down(): void
    {
        Schema::table('advisory_assignments', function (Blueprint $table) {
            $table->string('section')->default('');
        });
    }
};
