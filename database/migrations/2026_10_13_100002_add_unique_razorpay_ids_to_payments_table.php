<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NULL is intentionally allowed for abandoned/created attempts, but
        // existing non-null duplicates must never be hidden by this migration.
        foreach (['razorpay_order_id', 'razorpay_payment_id'] as $column) {
            $duplicate = DB::table('payments')
                ->select($column)
                ->whereNotNull($column)
                ->groupBy($column)
                ->havingRaw('COUNT(*) > 1')
                ->value($column);

            if ($duplicate !== null) {
                throw new RuntimeException("Cannot add unique {$column} constraint: duplicate value exists.");
            }
        }

        Schema::table('payments', function (Blueprint $table): void {
            // Nullable gateway IDs remain valid for created/failed attempts;
            // supported databases allow multiple NULL values in a unique index.
            $table->unique('razorpay_order_id');
            $table->unique('razorpay_payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropUnique(['razorpay_order_id']);
            $table->dropUnique(['razorpay_payment_id']);
        });
    }
};
