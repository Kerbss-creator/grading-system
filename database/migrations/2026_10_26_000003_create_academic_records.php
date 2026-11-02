<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('grade_level', 60);
            $table->string('school_year', 20);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['name', 'grade_level', 'school_year'], 'subjects_identity_unique');
        });
        Schema::create('subject_offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->restrictOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('section', 60); // ALL must be explicitly selected for a grade-wide offering.
            $table->string('meeting_days', 90)->nullable();
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->string('room', 90)->nullable();
            $table->timestamps();
            $table->unique(['subject_id', 'section'], 'offerings_subject_section_unique');
        });
        Schema::create('class_advisers', function (Blueprint $table) {
            $table->id();
            $table->string('grade_level',60);
            $table->string('section',60);
            $table->string('school_year',20);
            $table->foreignId('teacher_id')->constrained('teachers')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['grade_level','section','school_year'],'class_advisers_group_unique');
        });
        Schema::create('grade_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('subject_offering_id')->constrained('subject_offerings')->restrictOnDelete();
            $table->unsignedTinyInteger('quarter');
            $table->decimal('grade', 5, 2)->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'subject_offering_id', 'quarter'], 'grades_once_per_quarter');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_records');
        Schema::dropIfExists('class_advisers');
        Schema::dropIfExists('subject_offerings');
        Schema::dropIfExists('subjects');
    }
};
