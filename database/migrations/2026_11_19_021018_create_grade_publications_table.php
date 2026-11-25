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
        Schema::create('grade_publications', function (Blueprint $table) {
            $table->id();

            // Academic period
            $table->string('school_year', 9);
            $table->unsignedTinyInteger('term');

            // Administrator who published the grades
            $table->foreignId('published_by')
                ->constrained('users')
                ->restrictOnDelete();

            // Publication date and time
            $table->timestamp('published_at');

            $table->timestamps();

            // Only one publication record per school year and term
            $table->unique(
                ['school_year', 'term'],
                'grade_publications_year_term_unique'
            );

            $table->index(['school_year', 'term']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_publications');
    }
};
