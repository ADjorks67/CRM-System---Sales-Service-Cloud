<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('object_type', 32);
            $table->json('definition');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('owner_id');
            $table->index('object_type');
            $table->index('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
    }
};
