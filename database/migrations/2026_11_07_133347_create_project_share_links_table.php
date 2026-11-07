<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A read-only weekly update a project manager sends to the client by
     * WhatsApp or email. The client opens it without logging in. The token is
     * looked up by its hash and kept encrypted so the link can be copied again.
     */
    public function up(): void
    {
        Schema::create('project_share_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('token_hash', 64)->unique();
            $table->text('token');
            $table->date('week_start');
            $table->date('week_end');
            $table->text('message')->nullable();
            $table->boolean('include_photos')->default(true);
            $table->boolean('include_labour')->default(true);
            $table->boolean('include_notes')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_share_links');
    }
};
