<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customer "success stories" — submitted by a signed-in user from inside the
 * app, then reviewed by a site administrator. Only approved rows are shown on
 * the public homepage and reviews page.
 *
 * `company_id` is nullable and NOT a hard tenant scope: a story is a public
 * marketing asset, not tenant data. We store the company for context/audit
 * but the moderation screen (gated to the site-admin email) is the only place
 * these rows are read on the private side.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('success_stories', function (Blueprint $table) {
            $table->id();

            // Who it's about — captured at submit time so the story stays
            // intact even if the user or company is later removed.
            $table->string('name');
            $table->string('role')->nullable();          // e.g. "Projects Director"
            $table->string('organisation')->nullable();  // company name shown publicly
            $table->string('email')->nullable();          // contact only, never published
            $table->text('story');

            // 1–5 stars (optional; used to render the quote in the grid).
            $table->unsignedTinyInteger('rating')->nullable();

            // Moderation state.
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending')->index();
            $table->boolean('is_featured')->default(false); // pin to homepage spotlight
            $table->timestamp('published_at')->nullable();

            // The submitter, for audit. Nullable + nullOnDelete so removing an
            // account never deletes an approved, published story.
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('success_stories');
    }
};
