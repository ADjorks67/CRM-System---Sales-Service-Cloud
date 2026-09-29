<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sharing_defaults', function (Blueprint $table) {
            $table->id();
            $table->string('object_type', 64)->unique();
            $table->string('access_level', 32);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sharing_defaults');
    }
};
