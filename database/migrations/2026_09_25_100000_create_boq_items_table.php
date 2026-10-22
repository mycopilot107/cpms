<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BOQ (Bill of Quantities) line items — the priced breakdown of the work
     * for a project. Each row belongs to a company (tenant root) and a project.
     * `amount` is derived server-side (quantity * rate) and stored so reports
     * can SUM it directly.
     */
    public function up(): void
    {
        Schema::create('boq_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();

            $table->string('item_code')->nullable();
            $table->string('category')->nullable();       // e.g. Foundation, RCC, Finishing
            $table->string('description');
            $table->string('unit')->nullable();           // Cum, Sqm, Nos, Kg, Rmt, LS ...

            $table->decimal('quantity', 15, 3)->default(0);
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('amount', 18, 2)->default(0); // quantity * rate (server-computed)

            $table->unsignedInteger('sort_order')->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['company_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boq_items');
    }
};
