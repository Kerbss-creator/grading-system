<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('advisory_assignments', 'section')) {
            Schema::table('advisory_assignments', function (Blueprint $table) {
                $table->string('section')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Keep section column to preserve advisory assignment structure.
    }
};