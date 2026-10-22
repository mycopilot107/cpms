<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Public "get in touch" submissions — both the Contact form and the
 * Customer-feedback form write here. Deliberately NOT tenant-scoped: these
 * come from anonymous visitors on the public marketing site, before any
 * company/user exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            // Distinguishes a contact enquiry from a product feedback/review.
            $table->enum('type', ['contact', 'feedback'])->default('contact')->index();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            // Star rating 1–5, only used by the feedback form.
            $table->unsignedTinyInteger('rating')->nullable();
            // Light audit trail for spam triage; no personal profiling.
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
