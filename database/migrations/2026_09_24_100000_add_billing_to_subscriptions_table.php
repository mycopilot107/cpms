<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extend `subscriptions` with the paid-plan details.
 *
 * The MVP table only tracked the trial. A paid subscription additionally
 * records how many user seats were purchased, for how many years, and the
 * amount charged — enough to render "you're on the 15-user / 2-year plan"
 * and to enforce the seat cap without touching the payments ledger.
 *
 * All columns are nullable so existing trial rows are untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedInteger('seats')->nullable()->after('plan');
            $table->unsignedTinyInteger('billing_years')->nullable()->after('seats');
            $table->unsignedInteger('price_per_user')->nullable()->after('billing_years');
            $table->unsignedBigInteger('amount')->nullable()->after('price_per_user');
            $table->string('currency', 3)->default('INR')->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'seats',
                'billing_years',
                'price_per_user',
                'amount',
                'currency',
            ]);
        });
    }
};
