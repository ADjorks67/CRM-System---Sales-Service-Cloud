<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Archive retention (NFR-SCAL-002)
    |--------------------------------------------------------------------------
    |
    | Closed opportunities older than this many days are archived by
    | `php artisan crm:archive-old-records` (sets archived_at when null).
    |
    */
    'archive' => [
        'closed_opportunity_days' => (int) env('CRM_ARCHIVE_CLOSED_OPPORTUNITY_DAYS', 365),
    ],

    /*
    |--------------------------------------------------------------------------
    | Large dataset seed defaults (Phase 6 performance)
    |--------------------------------------------------------------------------
    |
    | Used by LargeDatasetSeeder. Override via artisan options or env.
    | Do not call from DatabaseSeeder — intentional opt-in only.
    |
    */
    'large_seed' => [
        'accounts' => (int) env('CRM_LARGE_SEED_ACCOUNTS', 1000),
        'contacts_per_account' => (int) env('CRM_LARGE_SEED_CONTACTS_PER_ACCOUNT', 2),
        'leads' => (int) env('CRM_LARGE_SEED_LEADS', 2000),
        'opportunities' => (int) env('CRM_LARGE_SEED_OPPORTUNITIES', 2000),
        'chunk' => (int) env('CRM_LARGE_SEED_CHUNK', 500),
    ],

];
