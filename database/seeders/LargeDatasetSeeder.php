<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Opt-in performance seed (Phase 6 / NFR-PERF). Never called from DatabaseSeeder.
 *
 * Example:
 *   php artisan db:seed --class=LargeDatasetSeeder
 *   CRM_LARGE_SEED_ACCOUNTS=50000 php artisan db:seed --class=LargeDatasetSeeder
 */
class LargeDatasetSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = max(1, (int) config('crm.large_seed.accounts', 1000));
        $contactsPerAccount = max(0, (int) config('crm.large_seed.contacts_per_account', 2));
        $leads = max(0, (int) config('crm.large_seed.leads', 2000));
        $opportunities = max(0, (int) config('crm.large_seed.opportunities', 2000));
        $chunk = max(50, (int) config('crm.large_seed.chunk', 500));

        $ownerId = User::query()->value('id') ?? User::factory()->create()->id;

        $this->command?->info("LargeDatasetSeeder: accounts={$accounts}, leads={$leads}, opportunities={$opportunities}");

        DB::connection()->disableQueryLog();

        for ($offset = 0; $offset < $accounts; $offset += $chunk) {
            $batch = min($chunk, $accounts - $offset);
            Account::factory()
                ->count($batch)
                ->state(['owner_id' => $ownerId])
                ->create()
                ->each(function (Account $account) use ($ownerId, $contactsPerAccount): void {
                    if ($contactsPerAccount < 1) {
                        return;
                    }

                    Contact::factory()
                        ->count($contactsPerAccount)
                        ->forAccount($account)
                        ->state(['owner_id' => $ownerId])
                        ->create();
                });

            $this->command?->info('Accounts progress: '.min($offset + $batch, $accounts)." / {$accounts}");
        }

        for ($offset = 0; $offset < $leads; $offset += $chunk) {
            $batch = min($chunk, $leads - $offset);
            Lead::factory()->count($batch)->state(['owner_id' => $ownerId])->create();
            $this->command?->info('Leads progress: '.min($offset + $batch, $leads)." / {$leads}");
        }

        $accountIds = Account::query()->orderByDesc('id')->limit(max(1, (int) ($accounts / 10)))->pluck('id');

        for ($offset = 0; $offset < $opportunities; $offset += $chunk) {
            $batch = min($chunk, $opportunities - $offset);
            Opportunity::factory()
                ->count($batch)
                ->state(fn () => [
                    'owner_id' => $ownerId,
                    'account_id' => $accountIds->random(),
                ])
                ->create();
            $this->command?->info('Opportunities progress: '.min($offset + $batch, $opportunities)." / {$opportunities}");
        }

        $this->command?->info('LargeDatasetSeeder finished.');
    }
}
