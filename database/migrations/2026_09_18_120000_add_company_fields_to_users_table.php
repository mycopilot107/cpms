<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Turns Laravel's default users table into a tenant-scoped, role-aware
     * users table. Runs AFTER the companies table exists (later timestamp),
     * so the company_id foreign key can be constrained safely.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tenant link. Nullable at the DB level so framework/system users
            // can exist, but the application always sets it for real users.
            // Deleting a company removes its users (true tenant ownership).
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained()          // references companies.id
                ->cascadeOnDelete();

            $table->enum('role', [
                'owner',
                'admin',
                'manager',
                'engineer',
                'supervisor',
                'worker',
                'accountant',
            ])->default('worker')->after('password');

            $table->string('phone')->nullable()->after('role');

            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active')->after('phone');

            // Common lookups: "all active admins in company X"
            $table->index(['company_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'role']);
            $table->dropConstrainedForeignId('company_id');
            $table->dropColumn(['role', 'phone', 'status']);
        });
    }
};
