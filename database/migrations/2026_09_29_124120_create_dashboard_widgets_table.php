<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_id')->constrained('dashboards')->cascadeOnDelete();
            $table->string('title', 120);
            $table->string('widget_type', 32);
            $table->string('source_type', 32);
            $table->string('source_key', 120)->nullable();
            $table->foreignId('saved_report_id')->nullable()->constrained('saved_reports')->nullOnDelete();
            $table->unsignedTinyInteger('grid_x')->default(0);
            $table->unsignedTinyInteger('grid_y')->default(0);
            $table->unsignedTinyInteger('grid_w')->default(6);
            $table->unsignedTinyInteger('grid_h')->default(4);
            $table->json('config')->nullable();
            $table->timestamps();

            $table->index('dashboard_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_widgets');
    }
};
