<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunity_stage_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained('opportunities')->cascadeOnDelete();
            $table->string('from_stage', 64)->nullable();
            $table->string('to_stage', 64);
            $table->unsignedTinyInteger('probability');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index('opportunity_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_stage_histories');
    }
};
