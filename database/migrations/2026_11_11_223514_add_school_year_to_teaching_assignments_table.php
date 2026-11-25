<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Add school_year if it does not exist
        |--------------------------------------------------------------------------
        */

        if (!Schema::hasColumn('teaching_assignments', 'school_year')) {
            Schema::table('teaching_assignments', function (Blueprint $table) {
                $table->string('school_year')->nullable()->after('subject');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Preserve existing assignments under SY 2026-2027
        |--------------------------------------------------------------------------
        */

        DB::table('teaching_assignments')
            ->whereNull('school_year')
            ->update([
                'school_year' => '2026-2027'
            ]);

        /*
        |--------------------------------------------------------------------------
        | 3. Make school_year required
        |--------------------------------------------------------------------------
        */

        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->string('school_year')->nullable(false)->change();
        });

        /*
        |--------------------------------------------------------------------------
        | 4. Create a separate faculty_id index first
        |--------------------------------------------------------------------------
        | The faculty foreign key currently relies on the old composite
        | unique index. This separate index protects that relationship.
        */

        $facultyIndexExists = DB::table('information_schema.statistics')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'teaching_assignments')
            ->where('INDEX_NAME', 'teaching_assignments_faculty_id_index')
            ->exists();

        if (!$facultyIndexExists) {
            Schema::table('teaching_assignments', function (Blueprint $table) {
                $table->index(
                    'faculty_id',
                    'teaching_assignments_faculty_id_index'
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Remove the old unique index
        |--------------------------------------------------------------------------
        */

        $oldIndexExists = DB::table('information_schema.statistics')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'teaching_assignments')
            ->where('INDEX_NAME', 'teaching_assignment_unique')
            ->exists();

        if ($oldIndexExists) {
            Schema::table('teaching_assignments', function (Blueprint $table) {
                $table->dropUnique('teaching_assignment_unique');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Create the new school-year-based unique index
        |--------------------------------------------------------------------------
        */

        $newIndexExists = DB::table('information_schema.statistics')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'teaching_assignments')
            ->where('INDEX_NAME', 'teaching_assignment_year_unique')
            ->exists();

        if (!$newIndexExists) {
            Schema::table('teaching_assignments', function (Blueprint $table) {
                $table->unique(
                    ['faculty_id', 'grade_level', 'subject', 'school_year'],
                    'teaching_assignment_year_unique'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->dropUnique('teaching_assignment_year_unique');
        });

        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->unique(
                ['faculty_id', 'grade_level', 'subject'],
                'teaching_assignment_unique'
            );
        });

        Schema::table('teaching_assignments', function (Blueprint $table) {
            $table->dropColumn('school_year');
        });

        // Keep the faculty_id index because it supports the foreign key.
    }
};
