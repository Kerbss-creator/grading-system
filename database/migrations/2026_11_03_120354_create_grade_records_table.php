<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grade_records', function (Blueprint $table) {
            $table->id();

            // Student and subject assignment
            $table->foreignId('student_id')
                ->constrained('students')
                ->restrictOnDelete();

            $table->foreignId('teaching_assignment_id')
                ->constrained('teaching_assignments')
                ->restrictOnDelete();

            // Academic period
            $table->string('school_year');
            $table->unsignedTinyInteger('term');

            // Written Works computation
            $table->decimal('ww_ps', 6, 2)->nullable();
            $table->decimal('ww_ws', 6, 2)->nullable();

            // Performance Tasks computation
            $table->decimal('pt_ps', 6, 2)->nullable();
            $table->decimal('pt_ws', 6, 2)->nullable();

            // Examination computation
            $table->decimal('ex_ps', 6, 2)->nullable();
            $table->decimal('ex_ws', 6, 2)->nullable();

            // Computed grades
            $table->decimal('initial_grade', 5, 2)->nullable();
            $table->decimal('term_grade', 5, 2)->nullable();
            $table->string('descriptor')->nullable();

            // Approval workflow
            $table->string('status')->default('draft');

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('adviser_reviewed_at')->nullable();
            $table->timestamp('admin_reviewed_at')->nullable();

            $table->timestamps();

            // Prevent duplicate records for the same student,
            // subject assignment, school year, and term.
            $table->unique(
                [
                    'student_id',
                    'teaching_assignment_id',
                    'school_year',
                    'term'
                ],
                'grade_record_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_records');
    }
};
