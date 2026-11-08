<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('grade_level_subjects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('grade_level');

            $table->timestamps();

            $table->unique(
                ['subject_id', 'grade_level'],
                'grade_level_subject_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_level_subjects');
    }
};
