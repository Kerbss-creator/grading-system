
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('faculty_id')
                ->constrained('faculty')
                ->cascadeOnDelete();

            $table->string('grade_level');
            $table->string('subject');

            $table->timestamps();

            $table->unique(
                ['faculty_id', 'grade_level', 'subject'],
                'teaching_assignment_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_assignments');
    }
};
