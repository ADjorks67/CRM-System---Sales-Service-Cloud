<?php

namespace Database\Seeders;

use App\Models\Picklist;
use Illuminate\Database\Seeder;

class PicklistSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // Lead statuses (FR-LEAD-006)
            ['category' => 'lead_status', 'value' => 'new', 'label' => 'New', 'sort_order' => 1],
            ['category' => 'lead_status', 'value' => 'working', 'label' => 'Working', 'sort_order' => 2],
            ['category' => 'lead_status', 'value' => 'nurturing', 'label' => 'Nurturing', 'sort_order' => 3],
            ['category' => 'lead_status', 'value' => 'qualified', 'label' => 'Qualified', 'sort_order' => 4],
            ['category' => 'lead_status', 'value' => 'unqualified', 'label' => 'Unqualified', 'sort_order' => 5],
            ['category' => 'lead_status', 'value' => 'converted', 'label' => 'Converted', 'sort_order' => 6],

            // Lead sources (SRS-aligned)
            ['category' => 'lead_source', 'value' => 'advertisement', 'label' => 'Advertisement', 'sort_order' => 1],
            ['category' => 'lead_source', 'value' => 'external_referral', 'label' => 'External Referral', 'sort_order' => 2],
            ['category' => 'lead_source', 'value' => 'social', 'label' => 'Social', 'sort_order' => 3],
            ['category' => 'lead_source', 'value' => 'trade_show', 'label' => 'Trade Show', 'sort_order' => 4],
            ['category' => 'lead_source', 'value' => 'web', 'label' => 'Web', 'sort_order' => 5],
            ['category' => 'lead_source', 'value' => 'other', 'label' => 'Other', 'sort_order' => 6],

            // Account types (FR-ACCT-002)
            ['category' => 'account_type', 'value' => 'customer', 'label' => 'Customer', 'sort_order' => 1],
            ['category' => 'account_type', 'value' => 'prospect', 'label' => 'Prospect', 'sort_order' => 2],
            ['category' => 'account_type', 'value' => 'partner', 'label' => 'Partner', 'sort_order' => 3],
            ['category' => 'account_type', 'value' => 'other', 'label' => 'Other', 'sort_order' => 4],

            // Salutation
            ['category' => 'salutation', 'value' => 'mr', 'label' => 'Mr.', 'sort_order' => 1],
            ['category' => 'salutation', 'value' => 'ms', 'label' => 'Ms.', 'sort_order' => 2],
            ['category' => 'salutation', 'value' => 'mrs', 'label' => 'Mrs.', 'sort_order' => 3],
            ['category' => 'salutation', 'value' => 'dr', 'label' => 'Dr.', 'sort_order' => 4],
            ['category' => 'salutation', 'value' => 'prof', 'label' => 'Prof.', 'sort_order' => 5],

            // Rating
            ['category' => 'rating', 'value' => 'hot', 'label' => 'Hot', 'sort_order' => 1],
            ['category' => 'rating', 'value' => 'warm', 'label' => 'Warm', 'sort_order' => 2],
            ['category' => 'rating', 'value' => 'cold', 'label' => 'Cold', 'sort_order' => 3],

            // Industries
            ['category' => 'industry', 'value' => 'agriculture', 'label' => 'Agriculture', 'sort_order' => 1],
            ['category' => 'industry', 'value' => 'apparel', 'label' => 'Apparel', 'sort_order' => 2],
            ['category' => 'industry', 'value' => 'banking', 'label' => 'Banking', 'sort_order' => 3],
            ['category' => 'industry', 'value' => 'biotechnology', 'label' => 'Biotechnology', 'sort_order' => 4],
            ['category' => 'industry', 'value' => 'communications', 'label' => 'Communications', 'sort_order' => 5],
            ['category' => 'industry', 'value' => 'construction', 'label' => 'Construction', 'sort_order' => 6],
            ['category' => 'industry', 'value' => 'consulting', 'label' => 'Consulting', 'sort_order' => 7],
            ['category' => 'industry', 'value' => 'education', 'label' => 'Education', 'sort_order' => 8],
            ['category' => 'industry', 'value' => 'electronics', 'label' => 'Electronics', 'sort_order' => 9],
            ['category' => 'industry', 'value' => 'energy', 'label' => 'Energy', 'sort_order' => 10],
            ['category' => 'industry', 'value' => 'engineering', 'label' => 'Engineering', 'sort_order' => 11],
            ['category' => 'industry', 'value' => 'entertainment', 'label' => 'Entertainment', 'sort_order' => 12],
            ['category' => 'industry', 'value' => 'environmental', 'label' => 'Environmental', 'sort_order' => 13],
            ['category' => 'industry', 'value' => 'finance', 'label' => 'Finance', 'sort_order' => 14],
            ['category' => 'industry', 'value' => 'food_beverage', 'label' => 'Food & Beverage', 'sort_order' => 15],
            ['category' => 'industry', 'value' => 'government', 'label' => 'Government', 'sort_order' => 16],
            ['category' => 'industry', 'value' => 'healthcare', 'label' => 'Healthcare', 'sort_order' => 17],
            ['category' => 'industry', 'value' => 'hospitality', 'label' => 'Hospitality', 'sort_order' => 18],
            ['category' => 'industry', 'value' => 'insurance', 'label' => 'Insurance', 'sort_order' => 19],
            ['category' => 'industry', 'value' => 'machinery', 'label' => 'Machinery', 'sort_order' => 20],
            ['category' => 'industry', 'value' => 'manufacturing', 'label' => 'Manufacturing', 'sort_order' => 21],
            ['category' => 'industry', 'value' => 'media', 'label' => 'Media', 'sort_order' => 22],
            ['category' => 'industry', 'value' => 'not_for_profit', 'label' => 'Not For Profit', 'sort_order' => 23],
            ['category' => 'industry', 'value' => 'recreation', 'label' => 'Recreation', 'sort_order' => 24],
            ['category' => 'industry', 'value' => 'retail', 'label' => 'Retail', 'sort_order' => 25],
            ['category' => 'industry', 'value' => 'shipping', 'label' => 'Shipping', 'sort_order' => 26],
            ['category' => 'industry', 'value' => 'technology', 'label' => 'Technology', 'sort_order' => 27],
            ['category' => 'industry', 'value' => 'telecommunications', 'label' => 'Telecommunications', 'sort_order' => 28],
            ['category' => 'industry', 'value' => 'transportation', 'label' => 'Transportation', 'sort_order' => 29],
            ['category' => 'industry', 'value' => 'utilities', 'label' => 'Utilities', 'sort_order' => 30],
            ['category' => 'industry', 'value' => 'other', 'label' => 'Other', 'sort_order' => 31],

            // Opportunity stages + probabilities (meta_int)
            ['category' => 'opportunity_stage', 'value' => 'qualification', 'label' => 'Qualification', 'sort_order' => 1, 'meta_int' => 10],
            ['category' => 'opportunity_stage', 'value' => 'meeting_scheduled', 'label' => 'Meeting Scheduled', 'sort_order' => 2, 'meta_int' => 20],
            ['category' => 'opportunity_stage', 'value' => 'proposal_price_quote', 'label' => 'Proposal/Price Quote', 'sort_order' => 3, 'meta_int' => 65],
            ['category' => 'opportunity_stage', 'value' => 'negotiation_review', 'label' => 'Negotiation/Review', 'sort_order' => 4, 'meta_int' => 80],
            ['category' => 'opportunity_stage', 'value' => 'closed_won', 'label' => 'Closed Won', 'sort_order' => 5, 'meta_int' => 100],
            ['category' => 'opportunity_stage', 'value' => 'closed_lost', 'label' => 'Closed Lost', 'sort_order' => 6, 'meta_int' => 0],
        ];

        foreach ($rows as $row) {
            Picklist::query()->updateOrCreate(
                ['category' => $row['category'], 'value' => $row['value']],
                [
                    'label' => $row['label'],
                    'sort_order' => $row['sort_order'],
                    'meta_int' => $row['meta_int'] ?? null,
                    'is_active' => true,
                ],
            );
        }

        // Retire legacy lead_source values that are no longer SRS-aligned.
        Picklist::query()
            ->where('category', 'lead_source')
            ->whereIn('value', ['phone', 'partner', 'purchased_list'])
            ->update(['is_active' => false]);
    }
}
