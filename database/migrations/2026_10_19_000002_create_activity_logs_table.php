<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->index();
            $table->string('actor_name');
            $table->string('actor_email')->nullable();
            $table->string('action', 40)->index();
            $table->string('record_type', 32)->index();
            $table->string('record_key')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->json('details')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }
    public function down(): void { Schema::dropIfExists('activity_logs'); }
};
