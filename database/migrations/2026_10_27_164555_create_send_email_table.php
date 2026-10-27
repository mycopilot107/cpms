<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Outreach email queue. One row per address — the unique index is what
     * guarantees nobody is emailed twice.
     */
    public function up(): void
    {
        Schema::create('send_email', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('subject');
            $table->text('body');
            $table->string('search_query')->nullable();
            $table->boolean('is_send')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();

            $table->index('is_send');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('send_email');
    }
};
