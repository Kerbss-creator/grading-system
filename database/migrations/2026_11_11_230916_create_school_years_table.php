<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->string('year_name')->unique();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('upcoming');
            $table->unsignedTinyInteger('current_term')->default(1);
            $table->timestamps();
        });

        // Preserve the existing school year as the active year.
        DB::table('school_years')->insert([
            'year_name' => '2026-2027',
            'start_date' => null,
            'end_date' => null,
            'status' => 'active',
            'current_term' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('school_years');
    }
};
