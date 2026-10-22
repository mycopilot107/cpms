<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Editable subscription plans.
 *
 * Prices used to live only in config/billing.php, which meant changing a price
 * was a code deploy. These tables make the per-user price EDITABLE in the
 * database:
 *
 *   plans        — one row per sellable plan (today just "Standard"), carrying
 *                  its billing period and which one is the active default.
 *   plan_prices  — the per-user price for a plan in each currency (INR, USD),
 *                  one row per (plan, currency).
 *
 * Plans are GLOBAL (not tenant-scoped): the same catalogue of prices applies to
 * every company. config/billing.php keeps the seat/term choices and remains the
 * FALLBACK price source when no plan row exists (e.g. before this migration
 * runs), so public pricing never breaks.
 *
 * We seed one default plan from the current config values, idempotently, so the
 * app has working prices the moment this migration runs — no separate seeder
 * step required.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();          // stable key, e.g. "standard"
            $table->string('billing_period', 10)->default('month'); // month | year
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('plan_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->string('currency', 3);             // ISO code, e.g. INR / USD
            $table->unsignedInteger('price_per_user'); // whole units, per user per period
            $table->timestamps();

            $table->unique(['plan_id', 'currency']);
        });

        $this->seedDefaultPlan();
    }

    /**
     * Seed (or top up) the default "Standard" plan from config/billing.php.
     * Idempotent: safe to run more than once and never duplicates rows.
     */
    private function seedDefaultPlan(): void
    {
        $now = now();
        $code = 'standard';

        $existing = DB::table('plans')->where('code', $code)->first();

        if ($existing) {
            $planId = $existing->id;
        } else {
            $planId = DB::table('plans')->insertGetId([
                'name' => 'Standard',
                'code' => $code,
                'billing_period' => (string) config('billing.billing_period', 'month'),
                'is_active' => true,
                'is_default' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // One price row per configured currency, seeded from its config price.
        foreach ((array) config('billing.currencies', []) as $cur) {
            $ccode = strtoupper((string) ($cur['code'] ?? ''));
            if ($ccode === '') {
                continue;
            }

            $hasRow = DB::table('plan_prices')
                ->where('plan_id', $planId)
                ->where('currency', $ccode)
                ->exists();

            if (! $hasRow) {
                DB::table('plan_prices')->insert([
                    'plan_id' => $planId,
                    'currency' => $ccode,
                    'price_per_user' => (int) ($cur['price_per_user'] ?? 0),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_prices');
        Schema::dropIfExists('plans');
    }
};
