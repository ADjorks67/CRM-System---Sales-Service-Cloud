<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('description', 500)->nullable();
            $table->string('folder', 64)->default('private');
            $table->string('report_type', 64);
            $table->json('definition');
            $table->boolean('is_private')->default(true);
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('owner_id');
            $table->index('folder');
            $table->index('report_type');
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_reports');
    }
};
