<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\GdprAudit;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PersonalDataAnonymizeService
{
    /**
     * Anonymize PII while preserving row IDs for related history [NFR-SEC-005].
     */
    public function anonymize(string $subjectType, int $subjectId, User $admin): Model
    {
        return DB::transaction(function () use ($subjectType, $subjectId, $admin): Model {
            $subject = $this->resolve($subjectType, $subjectId);

            match ($subjectType) {
                'contact' => $this->anonymizeContact($subject),
                'lead' => $this->anonymizeLead($subject),
                'user' => $this->anonymizeUser($subject, $admin),
                default => throw new InvalidArgumentException("Unsupported subject type [{$subjectType}]."),
            };

            GdprAudit::query()->create([
                'admin_id' => $admin->id,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'action' => 'anonymize',
                'meta' => ['method' => 'redact_pii'],
            ]);

            return $subject->fresh();
        });
    }

    private function anonymizeContact(Contact $contact): void
    {
        $token = 'anon-'.$contact->id;

        $contact->forceFill([
            'salutation' => null,
            'first_name' => 'Anonymized',
            'middle_name' => null,
            'last_name' => $token,
            'title' => null,
            'department' => null,
            'phone' => null,
            'mobile' => null,
            'home_phone' => null,
            'other_phone' => null,
            'email' => $token.'@anonymized.invalid',
            'fax' => null,
            'assistant' => null,
            'asst_phone' => null,
            'mailing_street' => null,
            'mailing_city' => null,
            'mailing_state' => null,
            'mailing_postal_code' => null,
            'mailing_country' => null,
            'other_street' => null,
            'other_city' => null,
            'other_state' => null,
            'other_postal_code' => null,
            'other_country' => null,
            'birthdate' => null,
            'description' => null,
        ])->save();
    }

    private function anonymizeLead(Lead $lead): void
    {
        $token = 'anon-'.$lead->id;

        $lead->forceFill([
            'salutation' => null,
            'first_name' => 'Anonymized',
            'last_name' => $token,
            'company' => 'Anonymized',
            'title' => null,
            'email' => $token.'@anonymized.invalid',
            'phone' => null,
            'mobile' => null,
            'website' => null,
            'street' => null,
            'city' => null,
            'state' => null,
            'postal_code' => null,
            'country' => null,
            'description' => null,
        ])->save();
    }

    private function anonymizeUser(User $user, User $admin): void
    {
        if ($user->id === $admin->id) {
            throw new InvalidArgumentException('Administrators cannot anonymize their own account.');
        }

        $token = 'anon-'.$user->id;

        $user->forceFill([
            'name' => 'Anonymized User '.$token,
            'email' => $token.'@anonymized.invalid',
            'is_active' => false,
            'mfa_enabled' => false,
            'remember_token' => null,
        ])->save();
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
