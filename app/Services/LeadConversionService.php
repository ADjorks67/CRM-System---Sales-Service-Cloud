<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use App\Support\PicklistOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LeadConversionService
{
    public function __construct(private readonly StageService $stageService) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     account: Account,
     *     contact: Contact,
     *     opportunity: Opportunity|null,
     *     events_transferred: int,
     *     task_note: string|null
     * }
     */
    public function convert(Lead $lead, User $actor, array $data): array
    {
        if ($lead->isReadOnlyConverted()) {
            throw new InvalidArgumentException('Lead is already converted.');
        }

        return DB::transaction(function () use ($lead, $actor, $data): array {
            $account = $this->resolveAccount($lead, $actor, $data);
            $contact = $this->createContact($lead, $actor, $account);
            $opportunity = $this->maybeCreateOpportunity($lead, $actor, $account, $data);

            $lead->forceFill([
                'status' => LeadStatus::Converted,
                'is_converted' => true,
                'converted_account_id' => $account->id,
                'converted_contact_id' => $contact->id,
                'converted_opportunity_id' => $opportunity?->id,
                'converted_at' => now(),
            ]);
            $lead->save();

            $eventsTransferred = $this->transferOpenEvents($lead, $account, $contact, $opportunity);
            $taskNote = $this->transferOpenTasks($lead, $account, $contact, $opportunity);

            return [
                'account' => $account,
                'contact' => $contact,
                'opportunity' => $opportunity,
                'events_transferred' => $eventsTransferred,
                'task_note' => $taskNote,
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveAccount(Lead $lead, User $actor, array $data): Account
    {
        $action = $data['account_action'] ?? 'create';

        if ($action === 'match') {
            $accountId = (int) ($data['account_id'] ?? 0);
            $account = Account::query()->visibleTo($actor)->whereKey($accountId)->first();

            if ($account === null) {
                throw new InvalidArgumentException('Selected account is not available.');
            }

            return $account;
        }

        return Account::query()->create([
            'name' => $data['account_name'] ?? $lead->company,
            'phone' => $lead->phone,
            'website' => $lead->website,
            'industry' => $lead->industry,
            'annual_revenue' => $lead->annual_revenue,
            'employees' => $lead->number_of_employees,
            'type' => 'prospect',
            'billing_street' => $lead->street,
            'billing_city' => $lead->city,
            'billing_state' => $lead->state,
            'billing_postal_code' => $lead->postal_code,
            'billing_country' => $lead->country,
            'description' => $lead->description,
            'owner_id' => $lead->owner_id ?? $actor->id,
        ]);
    }

    private function createContact(Lead $lead, User $actor, Account $account): Contact
    {
        return Contact::query()->create([
            'account_id' => $account->id,
            'salutation' => $lead->salutation,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name ?: 'Unknown',
            'title' => $lead->title,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'mobile' => $lead->mobile,
            'lead_source' => $lead->lead_source,
            'mailing_street' => $lead->street,
            'mailing_city' => $lead->city,
            'mailing_state' => $lead->state,
            'mailing_postal_code' => $lead->postal_code,
            'mailing_country' => $lead->country,
            'description' => $lead->description,
            'owner_id' => $lead->owner_id ?? $actor->id,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function maybeCreateOpportunity(Lead $lead, User $actor, Account $account, array $data): ?Opportunity
    {
        if (empty($data['create_opportunity'])) {
            return null;
        }

        $opportunity = Opportunity::query()->create([
            'name' => $data['opportunity_name'] ?? ($lead->company.' — Opportunity'),
            'account_id' => $account->id,
            'amount' => $data['opportunity_amount'] ?? null,
            'close_date' => $data['opportunity_close_date'] ?? now()->addMonth()->toDateString(),
            'stage' => $data['opportunity_stage'] ?? 'qualification',
            'lead_source' => $lead->lead_source,
            'owner_id' => $lead->owner_id ?? $actor->id,
        ]);

        return $this->stageService->changeStage($opportunity, (string) $opportunity->stage, $actor);
    }

    private function transferOpenEvents(Lead $lead, Account $account, Contact $contact, ?Opportunity $opportunity): int
    {
        $relatedType = $opportunity !== null ? 'opportunity' : 'account';
        $relatedId = $opportunity?->id ?? $account->id;

        return Event::query()
            ->where('related_type', 'lead')
            ->where('related_id', $lead->id)
            ->where('ends_at', '>=', now())
            ->update([
                'related_type' => $relatedType,
                'related_id' => $relatedId,
                'name_contact_id' => $contact->id,
            ]);
    }

    private function transferOpenTasks(Lead $lead, Account $account, Contact $contact, ?Opportunity $opportunity): ?string
    {
        if (! class_exists('App\\Models\\Task')) {
            return 'Open tasks were not transferred (Task module pending Dev A).';
        }

        return null;
    }

    /**
     * @return Collection<int, Account>
     */
    public function matchingAccounts(Lead $lead, User $user): Collection
    {
        if ($lead->company === null || trim($lead->company) === '') {
            return collect();
        }

        return Account::query()
            ->visibleTo($user)
            ->where('name', 'ilike', trim($lead->company))
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    /**
     * @return array<string, string>
     */
    public function opportunityStageOptions(): array
    {
        return PicklistOptions::options('opportunity_stage');
    }
}
