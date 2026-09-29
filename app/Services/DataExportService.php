<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataExportService
{
    /**
     * @return array<string, string>
     */
    public function objects(): array
    {
        return [
            'accounts' => 'Accounts',
            'contacts' => 'Contacts',
            'leads' => 'Leads',
            'opportunities' => 'Opportunities',
        ];
    }

    public function downloadCsv(string $object, User $user): StreamedResponse
    {
        if (! isset($this->objects()[$object])) {
            abort(404);
        }

        $filename = $object.'-export-'.now()->format('Ymd-His').'.csv';

        return Response::streamDownload(function () use ($object, $user): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }

            [$headers, $query, $mapper] = $this->exportDefinition($object, $user);
            fputcsv($out, $headers);

            $query->orderBy('id')->chunk(200, function ($rows) use ($out, $mapper): void {
                foreach ($rows as $row) {
                    fputcsv($out, $mapper($row));
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{0: list<string>, 1: Builder, 2: callable}
     */
    private function exportDefinition(string $object, User $user): array
    {
        return match ($object) {
            'accounts' => [
                ['name', 'phone', 'website', 'type', 'industry', 'billing_city', 'billing_state', 'billing_country', 'owner'],
                Account::query()->visibleTo($user)->with('owner'),
                fn (Account $a) => [
                    $a->name, $a->phone, $a->website, $a->type, $a->industry,
                    $a->billing_city, $a->billing_state, $a->billing_country, $a->owner?->name,
                ],
            ],
            'contacts' => [
                ['first_name', 'last_name', 'email', 'phone', 'account_name', 'title', 'owner'],
                Contact::query()->visibleTo($user)->with(['owner', 'account']),
                fn (Contact $c) => [
                    $c->first_name, $c->last_name, $c->email, $c->phone,
                    $c->account?->name, $c->title, $c->owner?->name,
                ],
            ],
            'leads' => [
                ['first_name', 'last_name', 'company', 'email', 'phone', 'status', 'lead_source', 'owner'],
                Lead::query()->visibleTo($user)->with('owner'),
                fn (Lead $l) => [
                    $l->first_name, $l->last_name, $l->company, $l->email, $l->phone,
                    $l->status?->value ?? $l->status, $l->lead_source, $l->owner?->name,
                ],
            ],
            'opportunities' => [
                ['name', 'account_name', 'amount', 'close_date', 'stage', 'lead_source', 'owner'],
                Opportunity::query()->visibleTo($user)->notArchived()->with(['owner', 'account']),
                fn (Opportunity $o) => [
                    $o->name, $o->account?->name, $o->amount, $o->close_date?->toDateString(),
                    $o->stage, $o->lead_source, $o->owner?->name,
                ],
            ],
            default => abort(404),
        };
    }
}
