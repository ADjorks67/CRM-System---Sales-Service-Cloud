<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use App\Support\PicklistOptions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\File;

class DataImportService
{
    public function __construct(
        private readonly StageService $stageService,
    ) {}

    /**
     * @return array<string, array{label: string, fields: array<string, string>, model: class-string}>
     */
    public function objects(): array
    {
        return [
            'accounts' => [
                'label' => 'Accounts',
                'fields' => [
                    'name' => 'Account Name',
                    'phone' => 'Phone',
                    'website' => 'Website',
                    'type' => 'Type',
                    'industry' => 'Industry',
                    'billing_city' => 'Billing City',
                    'billing_state' => 'Billing State',
                    'billing_country' => 'Billing Country',
                    'description' => 'Description',
                ],
                'model' => Account::class,
            ],
            'contacts' => [
                'label' => 'Contacts',
                'fields' => [
                    'first_name' => 'First Name',
                    'last_name' => 'Last Name',
                    'email' => 'Email',
                    'phone' => 'Phone',
                    'account_name' => 'Account Name',
                    'title' => 'Title',
                ],
                'model' => Contact::class,
            ],
            'leads' => [
                'label' => 'Leads',
                'fields' => [
                    'first_name' => 'First Name',
                    'last_name' => 'Last Name',
                    'company' => 'Company',
                    'email' => 'Email',
                    'phone' => 'Phone',
                    'status' => 'Status',
                    'lead_source' => 'Lead Source',
                ],
                'model' => Lead::class,
            ],
            'opportunities' => [
                'label' => 'Opportunities',
                'fields' => [
                    'name' => 'Opportunity Name',
                    'account_name' => 'Account Name',
                    'amount' => 'Amount',
                    'close_date' => 'Close Date',
                    'stage' => 'Stage',
                    'lead_source' => 'Lead Source',
                ],
                'model' => Opportunity::class,
            ],
        ];
    }

    public function authorizeImport(User $user, string $object): bool
    {
        $model = $this->objects()[$object]['model'] ?? null;

        return $model !== null && $user->can('create', $model);
    }

    /**
     * @return list<string>
     */
    public function readHeaders(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('Unable to read CSV file.');
        }

        $headers = fgetcsv($handle) ?: [];
        fclose($handle);

        return array_map(fn ($h) => trim((string) $h), $headers);
    }

    /**
     * @param  list<string>  $headers
     * @return array<string, int|null> field => column index
     */
    public function suggestMapping(string $object, array $headers): array
    {
        $fields = $this->objects()[$object]['fields'] ?? [];
        $mapping = [];

        foreach ($fields as $field => $label) {
            $mapping[$field] = null;
            foreach ($headers as $index => $header) {
                $normalized = strtolower(trim($header));
                if ($normalized === strtolower($field) || $normalized === strtolower($label)) {
                    $mapping[$field] = $index;
                    break;
                }
            }
        }

        return $mapping;
    }

    /**
     * @param  array<string, int|string|null>  $mapping
     * @return array{created: int, updated: int, skipped: int, errors: list<array{row: int, message: string}>}
     */
    public function import(string $object, string $path, array $mapping, User $actor, bool $updateOrInsert = true): array
    {
        if (! isset($this->objects()[$object])) {
            throw ValidationException::withMessages(['object' => 'Unsupported import object.']);
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('Unable to read CSV file.');
        }

        fgetcsv($handle);
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            try {
                $payload = $this->mapRow($object, $row, $mapping, $actor);
                $result = DB::transaction(fn () => $this->upsertRow($object, $payload, $actor, $updateOrInsert));
                if ($result === 'created') {
                    $created++;
                } elseif ($result === 'updated') {
                    $updated++;
                } else {
                    $skipped++;
                }
            } catch (\Throwable $e) {
                $errors[] = [
                    'row' => $rowNumber,
                    'message' => $e->getMessage(),
                ];
            }
        }

        fclose($handle);

        return compact('created', 'updated', 'skipped', 'errors');
    }

    /**
     * Accept UploadedFile or stored path for callers that still hold the upload.
     *
     * @param  array<string, int|string|null>  $mapping
     * @return array{created: int, updated: int, skipped: int, errors: list<array{row: int, message: string}>}
     */
    public function importUploaded(string $object, UploadedFile|File $file, array $mapping, User $actor, bool $updateOrInsert = true): array
    {
        return $this->import($object, $file->getRealPath(), $mapping, $actor, $updateOrInsert);
    }

    /**
     * @param  list<string|null>  $row
     * @param  array<string, int|string|null>  $mapping
     * @return array<string, mixed>
     */
    private function mapRow(string $object, array $row, array $mapping, User $actor): array
    {
        $payload = [];
        foreach ($mapping as $field => $index) {
            if ($index === null || $index === '') {
                continue;
            }
            $payload[$field] = isset($row[(int) $index]) ? trim((string) $row[(int) $index]) : null;
            if ($payload[$field] === '') {
                $payload[$field] = null;
            }
        }

        $payload['owner_id'] = $actor->id;

        return $this->validatePayload($object, $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function validatePayload(string $object, array $payload): array
    {
        $rules = match ($object) {
            'accounts' => [
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:40'],
                'website' => ['nullable', 'string', 'max:255'],
                'type' => ['nullable', 'string', PicklistOptions::inRule('account_type')],
                'industry' => ['nullable', 'string', PicklistOptions::inRule('industry')],
                'billing_city' => ['nullable', 'string', 'max:80'],
                'billing_state' => ['nullable', 'string', 'max:80'],
                'billing_country' => ['nullable', 'string', 'max:80'],
                'description' => ['nullable', 'string'],
                'owner_id' => ['required', 'integer'],
            ],
            'contacts' => [
                'first_name' => ['nullable', 'string', 'max:40'],
                'last_name' => ['required', 'string', 'max:80'],
                'email' => ['nullable', 'email', 'max:80'],
                'phone' => ['nullable', 'string', 'max:40'],
                'account_name' => ['required', 'string', 'max:255'],
                'title' => ['nullable', 'string', 'max:128'],
                'owner_id' => ['required', 'integer'],
            ],
            'leads' => [
                'first_name' => ['nullable', 'string', 'max:40'],
                'last_name' => ['required', 'string', 'max:80'],
                'company' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:80'],
                'phone' => ['nullable', 'string', 'max:40'],
                'status' => ['nullable', 'string', Rule::in(array_keys(LeadStatus::editableOptions()))],
                'lead_source' => ['nullable', 'string', PicklistOptions::inRule('lead_source')],
                'owner_id' => ['required', 'integer'],
            ],
            'opportunities' => [
                'name' => ['required', 'string', 'max:120'],
                'account_name' => ['required', 'string', 'max:255'],
                'amount' => ['nullable', 'numeric', 'min:0'],
                'close_date' => ['required', 'date'],
                'stage' => ['required', 'string', PicklistOptions::inRule('opportunity_stage')],
                'lead_source' => ['nullable', 'string', PicklistOptions::inRule('lead_source')],
                'owner_id' => ['required', 'integer'],
            ],
            default => throw new RuntimeException('Unsupported object.'),
        };

        $validator = Validator::make($payload, $rules);
        if ($validator->fails()) {
            throw new RuntimeException($validator->errors()->first());
        }

        return $validator->validated();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function upsertRow(string $object, array $payload, User $actor, bool $updateOrInsert): string
    {
        return match ($object) {
            'accounts' => $this->upsertAccount($payload, $updateOrInsert),
            'contacts' => $this->upsertContact($payload, $actor, $updateOrInsert),
            'leads' => $this->upsertLead($payload, $updateOrInsert),
            'opportunities' => $this->upsertOpportunity($payload, $actor, $updateOrInsert),
            default => 'skipped',
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function upsertAccount(array $payload, bool $updateOrInsert): string
    {
        $existing = Account::query()->where('name', $payload['name'])->first();
        if ($existing && ! $updateOrInsert) {
            return 'skipped';
        }

        if ($existing) {
            $existing->update($payload);

            return 'updated';
        }

        Account::query()->create($payload);

        return 'created';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function upsertContact(array $payload, User $actor, bool $updateOrInsert): string
    {
        $account = Account::query()->where('name', $payload['account_name'])->first();
        if ($account === null) {
            $account = Account::query()->create([
                'name' => $payload['account_name'],
                'owner_id' => $actor->id,
            ]);
        }

        unset($payload['account_name']);
        $payload['account_id'] = $account->id;

        $existing = null;
        if (! empty($payload['email'])) {
            $existing = Contact::query()->where('email', $payload['email'])->first();
        }

        if ($existing && ! $updateOrInsert) {
            return 'skipped';
        }

        if ($existing) {
            $existing->update($payload);

            return 'updated';
        }

        Contact::query()->create($payload);

        return 'created';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function upsertLead(array $payload, bool $updateOrInsert): string
    {
        $payload['status'] = $payload['status'] ?? LeadStatus::New->value;

        $existing = null;
        if (! empty($payload['email'])) {
            $existing = Lead::query()->where('email', $payload['email'])->first();
        }

        if ($existing && ! $updateOrInsert) {
            return 'skipped';
        }

        if ($existing) {
            $existing->update($payload);

            return 'updated';
        }

        Lead::query()->create($payload);

        return 'created';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function upsertOpportunity(array $payload, User $actor, bool $updateOrInsert): string
    {
        $account = Account::query()->where('name', $payload['account_name'])->first();
        if ($account === null) {
            $account = Account::query()->create([
                'name' => $payload['account_name'],
                'owner_id' => $actor->id,
            ]);
        }

        $stage = $payload['stage'];
        unset($payload['account_name'], $payload['stage']);
        $payload['account_id'] = $account->id;

        $existing = Opportunity::query()
            ->where('name', $payload['name'])
            ->where('account_id', $account->id)
            ->first();

        if ($existing && ! $updateOrInsert) {
            return 'skipped';
        }

        if ($existing) {
            $existing->update($payload);
            if ($existing->stage !== $stage) {
                $this->stageService->changeStage($existing, $stage, $actor);
            } else {
                $existing->recomputeExpectedRevenue();
                $existing->save();
            }

            return 'updated';
        }

        $opportunity = Opportunity::query()->create($payload);
        $this->stageService->changeStage($opportunity, $stage, $actor);

        return 'created';
    }

    /**
     * @param  list<string|null>  $row
     */
    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }
}
