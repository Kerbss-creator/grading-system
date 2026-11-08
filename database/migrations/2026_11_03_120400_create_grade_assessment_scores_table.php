<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grade_assessment_scores', function (Blueprint $table) {
            $table->id();

            // Connected grade record
            $table->foreignId('grade_record_id')
                ->constrained('grade_records')
                ->cascadeOnDelete();

            // Assessment information
            $table->string('assessment_code');
            $table->enum('assessment_group', ['WW', 'PT', 'EX']);

            // Individual assessment scores
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('highest_possible_score', 8, 2);

            $table->timestamps();

            // Prevent duplicate assessment entries
            $table->unique(
                ['grade_record_id', 'assessment_code'],
                'grade_assessment_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_assessment_scores');
    }
};
