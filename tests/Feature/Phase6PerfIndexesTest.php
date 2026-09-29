<?php

use Illuminate\Support\Facades\DB;

/**
 * @return list<string>
 */
function phase6IndexNames(string $table): array
{
    return collect(DB::select(
        'select indexname from pg_indexes where schemaname = current_schema() and tablename = ?',
        [$table],
    ))->pluck('indexname')->all();
}

test('phase 6 performance indexes exist on hot-path tables', function () {
    $expected = [
        'tasks' => ['tasks_owner_status_due_index', 'tasks_status_due_index'],
        'opportunities' => ['opportunities_archive_lookup_index', 'opportunities_owner_stage_archived_index'],
        'cases' => ['cases_closed_at_index', 'cases_owner_status_index'],
        'leads' => ['leads_first_name_index', 'leads_owner_status_index'],
        'contacts' => ['contacts_first_name_index'],
        'events' => ['events_owner_starts_at_index'],
        'users' => ['users_role_id_index'],
        'gdpr_audits' => ['gdpr_audits_admin_id_index'],
    ];

    foreach ($expected as $table => $indexes) {
        $present = phase6IndexNames($table);

        foreach ($indexes as $index) {
            expect($present)->toContain($index);
        }
    }
});
