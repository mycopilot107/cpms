<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_tokens', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
        });

        // Legacy tokens have no bounded lifetime; require a fresh sign-in.
        DB::table('api_tokens')->update(['revoked_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('api_tokens', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'revoked_at']);
        });
    }
};
