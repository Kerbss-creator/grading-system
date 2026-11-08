<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE account_setup_tokens
            MODIFY expires_at TIMESTAMP NOT NULL
            DEFAULT CURRENT_TIMESTAMP
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE account_setup_tokens
            MODIFY expires_at TIMESTAMP NOT NULL
        ");
    }
};
