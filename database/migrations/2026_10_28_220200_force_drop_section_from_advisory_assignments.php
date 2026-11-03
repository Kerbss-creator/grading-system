
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('advisory_assignments', 'section')) {
            return;
        }

        // Add a supporting index for the faculty foreign key.
        DB::statement(
            'ALTER TABLE advisory_assignments
             ADD INDEX advisory_assignments_faculty_id_index (faculty_id)'
        );

        // Remove the old unique index that includes section.
        DB::statement(
            'ALTER TABLE advisory_assignments
             DROP INDEX advisory_assignment_unique'
        );

        // Remove the section column.
        DB::statement(
            'ALTER TABLE advisory_assignments DROP COLUMN section'
        );

        // Restore uniqueness without section.
        DB::statement(
            'ALTER TABLE advisory_assignments
             ADD UNIQUE INDEX advisory_assignment_unique
             (faculty_id, grade_level, school_year)'
        );
    }

    public function down(): void
    {
        // No automatic rollback.
    }
};
