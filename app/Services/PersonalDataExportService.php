<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\GdprAudit;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class PersonalDataExportService
{
    /**
     * Build a portable JSON payload for a data subject [NFR-SEC-005].
     *
     * @return array{subject_type: string, subject_id: int, exported_at: string, data: array<string, mixed>}
     */
    public function export(string $subjectType, int $subjectId, User $admin): array
    {
        $subject = $this->resolve($subjectType, $subjectId);

        $data = match ($subjectType) {
            'contact' => $this->contactPayload($subject),
            'lead' => $this->leadPayload($subject),
            'user' => $this->userPayload($subject),
            default => throw new InvalidArgumentException("Unsupported subject type [{$subjectType}]."),
        };

        GdprAudit::query()->create([
            'admin_id' => $admin->id,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'action' => 'export',
            'meta' => ['fields' => array_keys($data)],
        ]);

        return [
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'exported_at' => now()->toIso8601String(),
            'data' => $data,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contactPayload(Contact $contact): array
    {
        return $contact->only([
            'id',
            'account_id',
            'salutation',
            'first_name',
            'middle_name',
            'last_name',
            'title',
            'department',
            'phone',
            'mobile',
            'home_phone',
            'other_phone',
            'email',
            'fax',
            'mailing_street',
            'mailing_city',
            'mailing_state',
            'mailing_postal_code',
            'mailing_country',
            'birthdate',
            'description',
            'owner_id',
            'created_at',
            'updated_at',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function leadPayload(Lead $lead): array
    {
        return $lead->only([
            'id',
            'salutation',
            'first_name',
            'last_name',
            'company',
            'title',
            'email',
            'phone',
            'mobile',
            'status',
            'street',
            'city',
            'state',
            'postal_code',
            'country',
            'description',
            'owner_id',
            'is_converted',
            'created_at',
            'updated_at',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        return $user->only([
            'id',
            'name',
            'email',
            'role_id',
            'is_active',
            'mfa_enabled',
            'created_at',
            'updated_at',
        ]);
    }

    private function resolve(string $subjectType, int $subjectId): Model
    {
        return match ($subjectType) {
            'contact' => Contact::query()->findOrFail($subjectId),
            'lead' => Lead::query()->findOrFail($subjectId),
            'user' => User::query()->findOrFail($subjectId),
            default => throw new InvalidArgumentException("Unsupported subject type [{$subjectType}]."),
        };
    }
}
