<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6 / NFR-PERF + NFR-SCAL-002 — composite indexes for list, home, archive, and search hot paths.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['owner_id', 'status', 'due_date'], 'tasks_owner_status_due_index');
            $table->index(['status', 'due_date'], 'tasks_status_due_index');
        });

        Schema::table('opportunities', function (Blueprint $table) {
            $table->index(['is_closed', 'archived_at', 'close_date'], 'opportunities_archive_lookup_index');
            $table->index(['owner_id', 'stage', 'archived_at'], 'opportunities_owner_stage_archived_index');
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->index('closed_at', 'cases_closed_at_index');
            $table->index(['owner_id', 'status'], 'cases_owner_status_index');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->index('first_name', 'leads_first_name_index');
            $table->index(['owner_id', 'status'], 'leads_owner_status_index');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->index('first_name', 'contacts_first_name_index');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->index(['owner_id', 'starts_at'], 'events_owner_starts_at_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role_id', 'users_role_id_index');
        });

        Schema::table('gdpr_audits', function (Blueprint $table) {
            $table->index('admin_id', 'gdpr_audits_admin_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_owner_status_due_index');
            $table->dropIndex('tasks_status_due_index');
        });

        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropIndex('opportunities_archive_lookup_index');
            $table->dropIndex('opportunities_owner_stage_archived_index');
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->dropIndex('cases_closed_at_index');
            $table->dropIndex('cases_owner_status_index');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('leads_first_name_index');
            $table->dropIndex('leads_owner_status_index');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex('contacts_first_name_index');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('events_owner_starts_at_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_id_index');
        });

        Schema::table('gdpr_audits', function (Blueprint $table) {
            $table->dropIndex('gdpr_audits_admin_id_index');
        });
    }
};
