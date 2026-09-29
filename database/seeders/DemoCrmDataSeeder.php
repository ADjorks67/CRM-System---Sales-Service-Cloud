<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\User;
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

        Account::query()->updateOrCreate(
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

        Contact::query()->updateOrCreate(
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
    }
}
