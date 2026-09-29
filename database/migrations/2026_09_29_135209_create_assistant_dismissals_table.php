<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assistant_dismissals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('recommendation_key', 120);
            $table->timestamp('dismissed_at');
            $table->timestamps();

            $table->unique(['user_id', 'recommendation_key']);
            $table->index('dismissed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assistant_dismissals');
    }
};
