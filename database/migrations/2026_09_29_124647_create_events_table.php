<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Event schema only (Dev A). Calendar UI / routes owned by Dev B [FR-CAL-*].
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('subject', 255);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('all_day')->default(false);
            $table->string('location', 255)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_private')->default(false);
            $table->string('related_type', 64)->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('owner_id');
            $table->index('starts_at');
            $table->index('ends_at');
            $table->index(['related_type', 'related_id']);
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
