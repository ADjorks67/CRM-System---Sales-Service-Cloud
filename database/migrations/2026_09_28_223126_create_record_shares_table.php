<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('record_shares', function (Blueprint $table) {
            $table->id();
            $table->morphs('shareable');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('access_level', 32);
            $table->timestamps();

            $table->unique(['shareable_type', 'shareable_id', 'user_id'], 'record_shares_unique');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('record_shares');
    }
};
