<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique();
            $table->string('subject')->nullable();
            $table->string('status', 64)->default('new');
            $table->string('priority', 64)->default('medium');
            $table->string('origin', 64)->default('web');
            $table->string('type', 64)->nullable();
            $table->string('reason', 64)->nullable();
            $table->text('description')->nullable();
            $table->text('internal_comments')->nullable();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('web_email')->nullable();
            $table->string('web_company')->nullable();
            $table->string('web_name')->nullable();
            $table->string('web_phone', 40)->nullable();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('owner_id');
            $table->index('status');
            $table->index('priority');
            $table->index('case_number');
            $table->index('updated_at');
            $table->index('account_id');
            $table->index('contact_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};
