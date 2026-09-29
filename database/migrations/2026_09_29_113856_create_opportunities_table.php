<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->decimal('amount', 15, 2)->nullable();
            $table->date('close_date');
            $table->string('stage', 64);
            $table->unsignedTinyInteger('probability')->default(0);
            $table->string('type', 64)->nullable();
            $table->string('lead_source', 64)->nullable();
            $table->string('next_step', 255)->nullable();
            $table->text('description')->nullable();
            $table->decimal('expected_revenue', 15, 2)->nullable();
            $table->boolean('is_closed')->default(false);
            $table->boolean('is_won')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('owner_id');
            $table->index('stage');
            $table->index('close_date');
            $table->index('account_id');
            $table->index('archived_at');
            $table->index('updated_at');
            $table->index('lead_source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
