<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The currency a company records its own project money in. Existing
     * companies were all shown in rupees, so they keep INR.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('currency', 3)->default('INR')->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
