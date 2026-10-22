<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add social-login (OAuth) identity columns to the users table.
     *
     * A user may now sign in either with an email + password OR by linking a
     * Google / Facebook account. `provider` / `provider_id` are set only in
     * server-side code (never mass-assigned), matching how company_id / role /
     * status are handled — see the User model's $fillable note.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Which OAuth provider owns this account ('google' | 'facebook'),
            // or null for a classic email/password account.
            $table->string('provider')->nullable()->after('status');

            // The provider's own immutable user id. Combined with `provider`
            // this uniquely identifies a returning social user.
            $table->string('provider_id')->nullable()->after('provider');

            // Optional profile photo URL supplied by the provider.
            $table->string('avatar')->nullable()->after('provider_id');

            // One provider account maps to at most one Best CPMS user. MySQL allows
            // many rows where both columns are NULL, so email/password users are
            // unaffected.
            $table->unique(['provider', 'provider_id']);
        });

        // Social-only accounts have no local password, so the column must allow
        // NULL. Laravel 11+ changes columns natively (no doctrine/dbal needed).
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['provider', 'provider_id']);
            $table->dropColumn(['provider', 'provider_id', 'avatar']);
        });

        // Restore NOT NULL on password. Any social-only accounts would need a
        // backfilled placeholder hash before this could succeed in practice.
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable(false)->change();
        });
    }
};
