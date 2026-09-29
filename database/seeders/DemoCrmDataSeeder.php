<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Contact;
use App\Models\CrmCase;
use App\Models\Event;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\StageService;
use Illuminate\Database\Seeder;

class DemoCrmDataSeeder extends Seeder
{
    public function run(): void
    {
        $manager = User::query()->where('email', 'sales.manager@crm.test')->first();
        $rep = User::query()->where('email', 'sales.rep@crm.test')->first();
        $service = User::query()->where('email', 'service.rep@crm.test')->first();
        $readonly = User::query()->where('email', 'readonly@crm.test')->first();

        if (! $manager || ! $rep || ! $service || ! $readonly) {
            return;
        }

        $acme = Account::query()->updateOrCreate(
            ['name' => 'Acme Corporation', 'owner_id' => $manager->id],
            [
                'type' => 'customer',
                'industry' => 'technology',
                'phone' => '555-0100',
                'website' => 'https://acme.example',
                'billing_street' => '100 Market Street',
                'billing_city' => 'San Francisco',
                'billing_state' => 'CA',
                'billing_postal_code' => '94105',
                'billing_country' => 'USA',
                'shipping_street' => '100 Market Street',
                'shipping_city' => 'San Francisco',
                'shipping_state' => 'CA',
                'shipping_postal_code' => '94105',
                'shipping_country' => 'USA',
                'description' => 'Demo account for Sales Manager.',
            ],
        );

        $globex = Account::query()->updateOrCreate(
            ['name' => 'Globex Industries', 'owner_id' => $rep->id],
            [
                'type' => 'prospect',
                'industry' => 'manufacturing',
                'phone' => '555-0200',
                'billing_city' => 'Austin',
                'billing_state' => 'TX',
                'billing_country' => 'USA',
                'description' => 'Demo account for Sales Rep.',
            ],
        );

        $initech = Account::query()->updateOrCreate(
            ['name' => 'Initech Support Co', 'owner_id' => $service->id],
            [
                'type' => 'partner',
                'industry' => 'consulting',
                'phone' => '555-0300',
                'description' => 'Demo account for Service Rep.',
            ],
        );

        Account::query()->updateOrCreate(
            ['name' => 'Read Only Sample Account', 'owner_id' => $readonly->id],
            [
                'type' => 'other',
                'industry' => 'education',
                'description' => 'Visible to Read-Only user as owner.',
            ],
        );

        $jane = Contact::query()->updateOrCreate(
            ['email' => 'jane.doe@acme.example', 'account_id' => $acme->id],
            [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'title' => 'VP Sales',
                'phone' => '555-0101',
                'owner_id' => $manager->id,
            ],
        );

        Contact::query()->updateOrCreate(
            ['email' => 'john.smith@globex.example', 'account_id' => $globex->id],
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'title' => 'Buyer',
                'phone' => '555-0201',
                'owner_id' => $rep->id,
            ],
        );

        Lead::query()->updateOrCreate(
            ['email' => 'lead.hot@example.com', 'company' => 'Hot Prospects LLC'],
            [
                'first_name' => 'Alex',
                'last_name' => 'Rivera',
                'status' => 'working',
                'lead_source' => 'web',
                'rating' => 'hot',
                'owner_id' => $rep->id,
            ],
        );

        Lead::query()->updateOrCreate(
            ['email' => 'lead.nurture@example.com', 'company' => 'Nurture Co'],
            [
                'first_name' => 'Sam',
                'last_name' => 'Patel',
                'status' => 'nurturing',
                'lead_source' => 'trade_show',
                'rating' => 'warm',
                'owner_id' => $manager->id,
            ],
        );

        $stageService = app(StageService::class);

        $acmeOpp = Opportunity::query()->updateOrCreate(
            ['name' => 'Acme Enterprise Renewal', 'account_id' => $acme->id],
            [
                'amount' => 120000,
                'close_date' => now()->addMonths(2)->toDateString(),
                'type' => 'renewal',
                'lead_source' => 'external_referral',
                'next_step' => 'Send proposal',
                'owner_id' => $manager->id,
            ],
        );
        $stageService->changeStage($acmeOpp, 'negotiation_review', $manager);

        $globexOpp = Opportunity::query()->updateOrCreate(
            ['name' => 'Globex Plant Expansion', 'account_id' => $globex->id],
            [
                'amount' => 45000,
                'close_date' => now()->addMonths(1)->toDateString(),
                'type' => 'new_business',
                'lead_source' => 'web',
                'owner_id' => $rep->id,
            ],
        );
        $stageService->changeStage($globexOpp, 'proposal_price_quote', $rep);

        $wonOpp = Opportunity::query()->updateOrCreate(
            ['name' => 'Acme Pilot Win', 'account_id' => $acme->id],
            [
                'amount' => 25000,
                'close_date' => now()->subDays(10)->toDateString(),
                'type' => 'new_business',
                'lead_source' => 'advertisement',
                'owner_id' => $manager->id,
            ],
        );
        $stageService->changeStage($wonOpp, 'closed_won', $manager);

        CrmCase::query()->updateOrCreate(
            ['subject' => 'Cannot reset password', 'account_id' => $initech->id],
            [
                'status' => 'working',
                'priority' => 'high',
                'origin' => 'email',
                'type' => 'problem',
                'description' => 'User locked out of portal.',
                'owner_id' => $service->id,
            ],
        );

        CrmCase::query()->updateOrCreate(
            ['subject' => 'Billing question', 'account_id' => $acme->id],
            [
                'status' => 'closed',
                'priority' => 'low',
                'origin' => 'phone',
                'type' => 'question',
                'contact_id' => $jane->id,
                'description' => 'Resolved invoice clarification.',
                'closed_at' => now()->subDays(3),
                'owner_id' => $service->id,
            ],
        );

        Event::query()->updateOrCreate(
            ['subject' => 'Acme renewal sync', 'owner_id' => $manager->id],
            [
                'starts_at' => now()->setTime(10, 0),
                'ends_at' => now()->setTime(11, 0),
                'is_all_day' => false,
                'location' => 'Zoom',
                'show_as' => 'busy',
                'is_private' => false,
                'calendar_type' => 'my_events',
                'related_type' => 'opportunity',
                'related_id' => $acmeOpp->id,
                'name_contact_id' => $jane->id,
            ],
        );

        Event::query()->updateOrCreate(
            ['subject' => 'Globex site visit', 'owner_id' => $rep->id],
            [
                'starts_at' => now()->addDay()->setTime(14, 0),
                'ends_at' => now()->addDay()->setTime(16, 0),
                'is_all_day' => false,
                'location' => 'Globex HQ',
                'show_as' => 'busy',
                'is_private' => false,
                'calendar_type' => 'my_events',
                'related_type' => 'account',
                'related_id' => $globex->id,
            ],
        );
    }
}
