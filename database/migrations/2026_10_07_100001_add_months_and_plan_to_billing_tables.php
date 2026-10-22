<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Switch the billing term from YEARS to MONTHS and link each sale to a plan.
 *
 * The term the customer buys is now counted in months (1 / 3 / 6 / 12), so the
 * stored columns are renamed years → months and billing_years → billing_months
 * to match. We also record which `plan` was purchased and the `billing_period`
 * in force at the time, so a historical payment/subscription stays correct even
 * if the plan's price is edited later.
 *
 * The rename runs in its own schema statement, before the new columns are added,
 * so the ordering/`after()` of the new columns can reference the renamed name.
 * plan_id is a nullable FK (nullOnDelete) because older trial rows and legacy
 * paid rows have no plan, and we never want deleting a plan to delete history.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Rename the term columns on their own first.
        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('years', 'months');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->renameColumn('billing_years', 'billing_months');
        });

        // 2) Add plan linkage + the billing period the sale was made under.
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('plan_id')
                ->nullable()
                ->after('company_id')
                ->constrained('plans')
                ->nullOnDelete();

            $table->string('billing_period', 10)->default('month')->after('months');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('plan_id')
                ->nullable()
                ->after('plan')
                ->constrained('plans')
                ->nullOnDelete();

            $table->string('billing_period', 10)->default('month')->after('billing_months');
        });
    }

    public function down(): void
    {
        // Drop the added columns first (FK + plain), then undo the renames.
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
            $table->dropColumn('billing_period');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
            $table->dropColumn('billing_period');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('months', 'years');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->renameColumn('billing_months', 'billing_years');
        });
    }
};
