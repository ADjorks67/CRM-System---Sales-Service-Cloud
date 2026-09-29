<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('salutation', 32)->nullable();
            $table->string('first_name', 40)->nullable();
            $table->string('last_name', 80);
            $table->string('company', 255);
            $table->string('title', 128)->nullable();
            $table->string('email', 80)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('mobile', 40)->nullable();
            $table->string('status', 64)->default('new');
            $table->string('lead_source', 64)->nullable();
            $table->string('rating', 64)->nullable();
            $table->string('industry', 64)->nullable();
            $table->decimal('annual_revenue', 15, 2)->nullable();
            $table->unsignedInteger('number_of_employees')->nullable();
            $table->string('website', 255)->nullable();
            $table->string('street', 255)->nullable();
            $table->string('city', 80)->nullable();
            $table->string('state', 80)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 80)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_converted')->default(false);
            $table->foreignId('converted_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('converted_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            // Opportunities ship in Phase 3 — store id without FK until then [FR-LEAD-005].
            $table->unsignedBigInteger('converted_opportunity_id')->nullable();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('owner_id');
            $table->index('status');
            $table->index('last_name');
            $table->index('company');
            $table->index('email');
            $table->index('lead_source');
            $table->index('is_converted');
            $table->index('updated_at');
            $table->index('converted_opportunity_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
