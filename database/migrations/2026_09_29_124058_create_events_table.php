<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('subject', 255);
            $table->timestampTz('starts_at');
            $table->timestampTz('ends_at');
            $table->boolean('is_all_day')->default(false);
            $table->string('location', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('show_as', 32)->default('busy');
            $table->boolean('is_private')->default(false);
            $table->string('calendar_type', 32)->default('my_events');
            $table->string('color', 32)->nullable();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->nullableMorphs('related');
            $table->foreignId('name_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('owner_id');
            $table->index(['starts_at', 'ends_at']);
            $table->index('calendar_type');
            $table->index('is_private');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
