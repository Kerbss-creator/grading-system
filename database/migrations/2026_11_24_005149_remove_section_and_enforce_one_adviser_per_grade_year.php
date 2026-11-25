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
        Schema::table('advisory_assignments', function (Blueprint $table) {

            // Remove the old unique constraint.
            $table->dropUnique('advisory_assignment_unique');

            // Remove the unused section column.
            $table->dropColumn('section');

            // Enforce one adviser per grade level per school year.
            $table->unique(
                ['grade_level', 'school_year'],
                'advisory_grade_year_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advisory_assignments', function (Blueprint $table) {

            // Restore the section column as nullable.
            $table->string('section')->nullable();

            // Restore the previous unique constraint.
            $table->dropUnique('advisory_grade_year_unique');

            $table->unique(
                ['faculty_id', 'grade_level', 'school_year'],
                'advisory_assignment_unique'
            );
        });
    }
};
