<?php

namespace App\Http\Resources\Api\V1;

use App\Models\CrmCase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CrmCase */
class CrmCaseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'case_number' => $this->case_number,
            'subject' => $this->subject,
            'status' => $this->status,
            'priority' => $this->priority,
            'account_id' => $this->account_id,
            'contact_id' => $this->contact_id,
            'owner_id' => $this->owner_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
