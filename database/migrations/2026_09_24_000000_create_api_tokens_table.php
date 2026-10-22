<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Personal-access-style tokens for the mobile app.
 *
 * We store only a SHA-256 hash of each token (never the plaintext), the same
 * way Laravel Sanctum does, so a leaked database cannot be used to forge API
 * calls. Tokens cascade-delete with their owning user.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');                 // device label, e.g. "Flutter app"
            $table->string('token', 64)->unique();   // sha256 hex digest
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};
