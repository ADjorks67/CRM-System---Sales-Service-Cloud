<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->foreignId('parent_account_id')->nullable()->constrained('accounts')->restrictOnDelete();
            $table->string('phone', 40)->nullable();
            $table->string('fax', 40)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('type', 64)->nullable();
            $table->string('industry', 64)->nullable();
            $table->unsignedInteger('employees')->nullable();
            $table->decimal('annual_revenue', 15, 2)->nullable();
            $table->string('billing_street', 255)->nullable();
            $table->string('billing_city', 80)->nullable();
            $table->string('billing_state', 80)->nullable();
            $table->string('billing_postal_code', 20)->nullable();
            $table->string('billing_country', 80)->nullable();
            $table->string('shipping_street', 255)->nullable();
            $table->string('shipping_city', 80)->nullable();
            $table->string('shipping_state', 80)->nullable();
            $table->string('shipping_postal_code', 20)->nullable();
            $table->string('shipping_country', 80)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('name');
            $table->index('owner_id');
            $table->index('parent_account_id');
            $table->index('type');
            $table->index('industry');
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
