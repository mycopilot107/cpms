<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Project payments — cash movements tied to a construction project:
     * money received from the client (incoming) or paid to vendors /
     * subcontractors (outgoing).
     *
     * NOTE: this is deliberately separate from the `payments` table used by the
     * Razorpay subscription billing. That one records SaaS charges to the
     * company; this one records a company's own project cash flow.
     */
    public function up(): void
    {
        Schema::create('project_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();

            $table->enum('direction', ['incoming', 'outgoing'])->default('incoming');

            $table->string('party_name')->nullable();     // client, vendor or subcontractor
            $table->decimal('amount', 18, 2);
            $table->date('payment_date');

            $table->enum('method', [
                'cash',
                'bank_transfer',
                'cheque',
                'upi',
                'card',
                'other',
            ])->default('bank_transfer');

            $table->string('reference_no')->nullable();
            $table->string('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['company_id', 'project_id']);
            $table->index(['company_id', 'direction']);
            $table->index(['company_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_payments');
    }
};
