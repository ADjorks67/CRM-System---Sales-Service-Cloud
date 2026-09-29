<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ownership_histories', function (Blueprint $table) {
            $table->id();
            $table->morphs('ownable');
            $table->foreignId('previous_owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('new_owner_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->boolean('notify_new_owner')->default(false);
            $table->boolean('transfer_open_activities')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('previous_owner_id');
            $table->index('new_owner_id');
            $table->index('changed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ownership_histories');
    }
};
