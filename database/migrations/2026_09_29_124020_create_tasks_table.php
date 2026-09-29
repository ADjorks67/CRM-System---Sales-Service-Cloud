<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('subject', 255);
            $table->string('status', 64)->default('not_started');
            $table->string('priority', 64)->default('normal');
            $table->date('due_date')->nullable();
            $table->text('comments')->nullable();
            $table->boolean('reminder_set')->default(false);
            $table->timestamp('reminder_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->string('related_type', 64)->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('owner_id');
            $table->index('status');
            $table->index('priority');
            $table->index('due_date');
            $table->index(['related_type', 'related_id']);
            $table->index('updated_at');
            $table->index('reminder_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
