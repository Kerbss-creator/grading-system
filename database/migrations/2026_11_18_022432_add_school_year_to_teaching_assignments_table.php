<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create a separate index to support the faculty foreign key.
        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->index(
                'faculty_id',
                'teaching_assignments_faculty_id_index'
            );
        });

        // Remove the old unique constraint.
        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->dropUnique('teaching_assignment_unique');
        });

        // Add school year and update the unique constraint.
        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->string('school_year')->nullable()->after('subject');

            $table->unique(
                ['faculty_id', 'grade_level', 'subject', 'school_year'],
                'teaching_assignment_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->dropUnique('teaching_assignment_unique');
        });

        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->dropColumn('school_year');

            $table->unique(
                ['faculty_id', 'grade_level', 'subject'],
                'teaching_assignment_unique'
            );
        });

        // Keep the faculty index because it may support the foreign key.
    }
};
