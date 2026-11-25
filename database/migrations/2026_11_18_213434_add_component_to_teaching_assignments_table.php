<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add component column.
        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->string('component', 100)
                ->default('General')
                ->after('subject');
        });

        // 2. Classify existing MAPEH assignments.
        // Existing assignment IDs and relationships remain unchanged.
        DB::table('teaching_assignments')
            ->where('subject', 'MAPEH')
            ->update([
                'component' => 'Music and Arts',
            ]);

        // 3. Replace the old unique constraint.
        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->dropUnique('teaching_assignment_year_unique');

            // One assignment per grade, subject, component, and school year.
            $table->unique(
                ['grade_level', 'subject', 'component', 'school_year'],
                'teaching_assignment_component_year_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check whether reverting would create duplicate assignments.
        $hasDuplicates = DB::table('teaching_assignments')
            ->select('faculty_id', 'grade_level', 'subject', 'school_year')
            ->groupBy('faculty_id', 'grade_level', 'subject', 'school_year')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicates) {
            throw new RuntimeException(
                'Cannot reverse component migration: duplicate assignments exist.'
            );
        }

        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->dropUnique('teaching_assignment_component_year_unique');

            $table->unique(
                ['faculty_id', 'grade_level', 'subject', 'school_year'],
                'teaching_assignment_year_unique'
            );

            $table->dropColumn('component');
        });
    }
};
