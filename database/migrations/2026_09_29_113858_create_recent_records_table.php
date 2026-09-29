<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recent_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('viewable_type', 64);
            $table->unsignedBigInteger('viewable_id');
            $table->timestamp('viewed_at');
            $table->timestamps();

            $table->unique(['user_id', 'viewable_type', 'viewable_id']);
            $table->index(['user_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recent_records');
    }
};
