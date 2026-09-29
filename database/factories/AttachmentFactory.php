<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Attachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attachable_type' => 'account',
            'attachable_id' => Account::factory(),
            'original_name' => fake()->word().'.txt',
            'disk' => 'attachments',
            'path' => 'tests/'.fake()->uuid().'.txt',
            'mime_type' => 'text/plain',
            'size_bytes' => 12,
            'uploaded_by' => User::factory(),
            'scan_status' => Attachment::SCAN_CLEAN,
        ];
    }
}
