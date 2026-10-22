<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Payments ledger — one row per Razorpay checkout attempt.
 *
 * Flow: we create a row (status = created) together with a Razorpay order,
 * then on a verified callback we mark it paid and link it to the activated
 * subscription. Keeping the order/payment history separate from the
 * `subscriptions` entitlement table gives a clean audit trail (including
 * failed / abandoned attempts) and lets a company renew over time.
 *
 * company_id is stored so the row is tenant-scoped exactly like every other
 * owned model — a payment from company A can never be looked up by company B.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            // Linked once the payment succeeds and the subscription is created.
            $table->foreignId('subscription_id')
                ->nullable()
                ->constrained('subscriptions')
                ->nullOnDelete();

            // The admin who initiated the purchase (kept for audit only).
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Razorpay references.
            $table->string('razorpay_order_id')->nullable()->index();
            $table->string('razorpay_payment_id')->nullable()->index();
            $table->string('razorpay_signature')->nullable();

            // What was purchased and the server-computed price (never trusted
            // from the browser).
            $table->unsignedInteger('seats');
            $table->unsignedTinyInteger('years');
            $table->unsignedInteger('price_per_user');
            $table->unsignedBigInteger('amount');          // total, in whole rupees
            $table->string('currency', 3)->default('INR');

            // created → paid | failed
            $table->string('status')->default('created')->index();
            $table->string('receipt')->unique();           // our internal reference
            $table->string('failure_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
