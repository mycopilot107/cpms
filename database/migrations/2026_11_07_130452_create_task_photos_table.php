<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Site photos on a task, taken in the Android app or added from the web.
     * Files live on the private disk; only the stored paths are kept here.
     */
    public function up(): void
    {
        Schema::create('task_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('path');
            $table->string('thumb_path');
            $table->string('caption')->nullable();
            $table->unsignedInteger('size');
            $table->unsignedSmallInteger('width');
            $table->unsignedSmallInteger('height');
            $table->timestamp('taken_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_photos');
    }
};
