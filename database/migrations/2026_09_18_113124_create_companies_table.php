<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The "companies" table is the tenant root of the SaaS. Every other
     * business table (users, subscriptions, projects, tasks, ...) hangs off a
     * company via a company_id foreign key.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Company profile
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();

            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('India');

            $table->string('gst_number')->nullable();   // Registration / GST / VAT number (optional)
            $table->string('logo')->nullable();          // Stored path/URL to the uploaded logo

            // Lifecycle status of the tenant itself
            $table->enum('status', [
                'active',
                'suspended',
                'inactive',
            ])->default('active');

            // 15-day free trial window (mirrored on the subscription record)
            $table->timestamp('trial_started_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
