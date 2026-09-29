<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE SEQUENCE IF NOT EXISTS case_number_seq START 1 INCREMENT 1');
    }

    public function down(): void
    {
        DB::statement('DROP SEQUENCE IF EXISTS case_number_seq');
    }
};
