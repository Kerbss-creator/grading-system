<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Delete dummy MAPEH grade records.
     */
    public function up(): void
    {
        // Get all existing MAPEH teaching assignment IDs.
        $mapehAssignmentIds = DB::table('teaching_assignments')
            ->where('subject', 'MAPEH')
            ->pluck('id');

        // Delete only MAPEH grade records.
        // Related assessment scores are deleted through the
        // existing grade_assessment_scores foreign key cascade.
        DB::table('grade_records')
            ->whereIn('teaching_assignment_id', $mapehAssignmentIds)
            ->delete();
    }

    /**
     * Deleted test records cannot be restored automatically.
     * Use the database backup if restoration is necessary.
     */
    public function down(): void
    {
        // Intentionally left empty.
    }
};
