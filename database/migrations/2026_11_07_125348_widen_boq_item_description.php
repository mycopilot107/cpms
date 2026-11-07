<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * BOQ descriptions from contractor bills often run past 255 characters
     * ("Providing and laying in position cement concrete of specified grade…").
     */
    public function up(): void
    {
        Schema::table('boq_items', function (Blueprint $table) {
            $table->text('description')->change();
        });
    }

    public function down(): void
    {
        Schema::table('boq_items', function (Blueprint $table) {
            $table->string('description')->change();
        });
    }
};
