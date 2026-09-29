<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('picklists', function (Blueprint $table) {
            $table->id();
            $table->string('category', 64);
            $table->string('value', 80);
            $table->string('label', 120);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedTinyInteger('meta_int')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['category', 'value']);
            $table->index(['category', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('picklists');
    }
};
