<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Project expenses — actual money spent against a project, categorised so a
     * cost report can break spend down by type. Tenant-scoped via company_id.
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();

            $table->enum('category', [
                'material',
                'labour',
                'equipment',
                'transport',
                'subcontractor',
                'overhead',
                'other',
            ])->default('other');

            $table->string('description');
            $table->decimal('amount', 18, 2);
            $table->date('expense_date');

            $table->string('vendor')->nullable();
            $table->string('reference_no')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['company_id', 'project_id']);
            $table->index(['company_id', 'category']);
            $table->index(['company_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
