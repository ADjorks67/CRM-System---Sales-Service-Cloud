<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->string('salutation', 32)->nullable();
            $table->string('first_name', 40)->nullable();
            $table->string('middle_name', 40)->nullable();
            $table->string('last_name', 80);
            $table->string('title', 128)->nullable();
            $table->string('department', 80)->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('mobile', 40)->nullable();
            $table->string('home_phone', 40)->nullable();
            $table->string('other_phone', 40)->nullable();
            $table->string('email', 80)->nullable();
            $table->string('fax', 40)->nullable();
            $table->foreignId('reports_to_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->string('assistant', 80)->nullable();
            $table->string('asst_phone', 40)->nullable();
            $table->string('mailing_street', 255)->nullable();
            $table->string('mailing_city', 80)->nullable();
            $table->string('mailing_state', 80)->nullable();
            $table->string('mailing_postal_code', 20)->nullable();
            $table->string('mailing_country', 80)->nullable();
            $table->string('other_street', 255)->nullable();
            $table->string('other_city', 80)->nullable();
            $table->string('other_state', 80)->nullable();
            $table->string('other_postal_code', 20)->nullable();
            $table->string('other_country', 80)->nullable();
            $table->string('lead_source', 64)->nullable();
            $table->date('birthdate')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('account_id');
            $table->index('owner_id');
            $table->index('reports_to_id');
            $table->index('last_name');
            $table->index('email');
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
